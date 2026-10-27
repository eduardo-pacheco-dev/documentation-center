const Toasts = (() => {
    const DEFAULT_DURATION = 5000;

    const findContainer = () => {
        let container = document.querySelector('[data-toast-container]');

        if (!container) {
            container = document.createElement('div');
            container.setAttribute('data-toast-container', '');
            container.className =
                'pointer-events-none fixed inset-x-0 top-4 z-[100] flex flex-col items-center gap-3 px-4 sm:inset-x-auto sm:right-4 sm:items-end';
            document.body.appendChild(container);
        }

        return container;
    };

    const dismiss = (toast) => {
        if (!toast) {
            return;
        }

        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-[-0.5rem]', 'opacity-0');

        setTimeout(() => toast.remove(), 200);
    };

    const setProgress = (toast, value) => {
        const wrapper = toast.querySelector('[data-toast-progress]');
        const bar = toast.querySelector('[data-toast-progress-bar]');

        if (!wrapper || !bar) {
            return;
        }

        if (value === null || value === undefined) {
            wrapper.classList.add('hidden');

            return;
        }

        wrapper.classList.remove('hidden');
        bar.style.width = Math.max(0, Math.min(100, value)) + '%';
    };

    const update = (toast, { message, progress } = {}) => {
        if (!toast) {
            return;
        }

        if (typeof message === 'string') {
            toast.querySelector('[data-toast-message]').textContent = message;
        }

        if (progress !== undefined) {
            setProgress(toast, progress);
        }
    };

    const show = ({ type = 'info', message = '', duration = DEFAULT_DURATION, progress = null } = {}) => {
        const template = document.querySelector('[data-toast-template]');

        if (!template || !message) {
            return null;
        }

        const toast = template.content.firstElementChild.cloneNode(true);

        toast.querySelectorAll('[data-toast-icon]').forEach((icon) => {
            icon.classList.toggle('hidden', icon.dataset.toastIcon !== type);
        });

        toast.querySelector('[data-toast-message]').textContent = message;
        setProgress(toast, progress);
        toast.querySelector('[data-toast-close]')?.addEventListener('click', () => dismiss(toast));

        findContainer().appendChild(toast);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-[-0.5rem]', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });
        });

        if (duration > 0) {
            setTimeout(() => dismiss(toast), duration);
        }

        return toast;
    };

    return {
        show,
        update,
        dismiss,
        success: (message, options = {}) => show({ ...options, type: 'success', message }),
        error: (message, options = {}) => show({ ...options, type: 'error', message }),
        info: (message, options = {}) => show({ ...options, type: 'info', message }),
    };
})();

window.toasts = Toasts;
window.toast = (options) => Toasts.show(typeof options === 'string' ? { message: options } : options);

(window.flashToasts || []).forEach((toast) => Toasts.show(toast));
window.flashToasts = { push: (toast) => Toasts.show(toast) };

const Exports = (() => {
    const filenameFrom = (link, response) => {
        const disposition = response.headers.get('Content-Disposition') || '';
        const match = disposition.match(/filename\*?=(?:UTF-8'')?"?([^";]+)"?/i);

        if (match) {
            return decodeURIComponent(match[1]);
        }

        return link.dataset.exportFilename || 'download.xlsx';
    };

    const trigger = async (link) => {
        if (link.dataset.exportBusy === 'true') {
            return;
        }

        link.dataset.exportBusy = 'true';
        link.setAttribute('aria-busy', 'true');

        const toast = Toasts.show({ type: 'info', message: 'Gerando planilha...', duration: 0, progress: 0 });

        try {
            const response = await fetch(link.href, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('Export failed with status ' + response.status);
            }

            const total = Number(response.headers.get('Content-Length')) || 0;
            const chunks = [];
            let received = 0;
            const reader = response.body?.getReader();

            if (reader) {
                while (true) {
                    const { done, value } = await reader.read();

                    if (done) {
                        break;
                    }

                    chunks.push(value);
                    received += value.length;

                    if (total > 0) {
                        const percent = Math.min(99, Math.round((received / total) * 100));
                        Toasts.update(toast, { message: 'Gerando planilha... ' + percent + '%', progress: percent });
                    }
                }
            } else {
                chunks.push(new Uint8Array(await response.arrayBuffer()));
            }

            const blob = new Blob(chunks, { type: response.headers.get('Content-Type') || 'application/octet-stream' });
            const url = URL.createObjectURL(blob);
            const anchor = document.createElement('a');

            anchor.href = url;
            anchor.download = filenameFrom(link, response);
            document.body.appendChild(anchor);
            anchor.click();
            anchor.remove();
            window.setTimeout(() => URL.revokeObjectURL(url), 1000);

            Toasts.dismiss(toast);
            Toasts.success('Planilha gerada com sucesso.');
        } catch (error) {
            Toasts.dismiss(toast);
            Toasts.error('Não foi possível gerar a planilha.');
        } finally {
            link.dataset.exportBusy = 'false';
            link.removeAttribute('aria-busy');
        }
    };

    return { trigger };
})();

document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    const link = event.target.closest('[data-export-link]');

    if (!link) {
        return;
    }

    event.preventDefault();
    Exports.trigger(link);
});

const ConfirmModal = (() => {
    let resolvePending = null;
    let lastFocus = null;

    const element = () => document.getElementById('confirm-modal');

    const close = (result) => {
        const modal = element();

        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        if (resolvePending) {
            resolvePending(result);
            resolvePending = null;
        }

        lastFocus?.focus?.();
        lastFocus = null;
    };

    const open = (options = {}) => {
        const modal = element();

        if (!modal) {
            return Promise.resolve(window.confirm(options.message || ''));
        }

        modal.querySelector('[data-confirm-title]').textContent = options.title || 'Tem certeza?';
        modal.querySelector('[data-confirm-message]').textContent = options.message || '';
        modal.querySelector('[data-confirm-accept]').textContent = options.confirmLabel || 'Confirmar';

        lastFocus = document.activeElement;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        modal.querySelector('button[data-confirm-cancel]')?.focus();

        return new Promise((resolve) => {
            resolvePending = resolve;
        });
    };

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-confirm-accept]')) {
            event.preventDefault();
            close(true);
        } else if (event.target.closest('[data-confirm-cancel]')) {
            event.preventDefault();
            close(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        const modal = element();

        if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            close(false);
        }
    });

    return { open };
})();

window.confirmAction = (options) => ConfirmModal.open(typeof options === 'string' ? { message: options } : options);

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.matches('form[data-confirm], form[data-confirm-delete]')) {
        return;
    }

    if (form.dataset.confirmBypass === 'true') {
        delete form.dataset.confirmBypass;

        return;
    }

    event.preventDefault();

    window.confirmAction({
        title: form.dataset.confirmTitle || 'Tem certeza?',
        message: form.dataset.confirm || form.dataset.confirmDelete || 'Tem certeza que deseja remover este item?',
        confirmLabel: form.dataset.confirmLabel || 'Confirmar',
    }).then((confirmed) => {
        if (!confirmed) {
            return;
        }

        form.dataset.confirmBypass = 'true';
        form.requestSubmit();
    });
});

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm], [data-confirm-delete]');

    if (!trigger || trigger.matches('form')) {
        return;
    }

    const form = trigger.form || trigger.closest('form');

    if (!form || form.matches('form[data-confirm], form[data-confirm-delete]')) {
        return;
    }

    event.preventDefault();

    window.confirmAction({
        title: trigger.dataset.confirmTitle || 'Tem certeza?',
        message: trigger.dataset.confirm || trigger.dataset.confirmDelete || 'Tem certeza que deseja remover este item?',
        confirmLabel: trigger.dataset.confirmLabel || 'Confirmar',
    }).then((confirmed) => {
        if (!confirmed) {
            return;
        }

        form.requestSubmit(trigger);
    });
}, true);

// Elements carrying data-copy copy its value to the clipboard and report back
// with a toast. The async clipboard API only exists in secure contexts, so fall
// back to a hidden textarea (and finally to a prompt) elsewhere, e.g. over http.
const copyToClipboard = async (value) => {
    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(value);

        return;
    }

    const textarea = document.createElement('textarea');

    textarea.value = value;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.top = '-9999px';
    textarea.style.opacity = '0';

    document.body.appendChild(textarea);
    textarea.select();

    const copied = document.execCommand('copy');

    textarea.remove();

    if (!copied) {
        throw new Error('Clipboard is unavailable');
    }
};

document.addEventListener('click', async (event) => {
    const trigger = event.target.closest('[data-copy]');

    if (!trigger) {
        return;
    }

    try {
        await copyToClipboard(trigger.dataset.copy || '');
        Toasts.success(trigger.dataset.copyMessage || 'Copiado para a área de transferência');
    } catch (error) {
        window.prompt('Copie:', trigger.dataset.copy || '');
    }
});

const DocumentPreview = (() => {
    const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'bmp'];
    const TEXT_EXTENSIONS = ['txt', 'md', 'json', 'csv', 'log', 'xml', 'html', 'css', 'js', 'php'];

    const element = () => document.getElementById('preview-modal');

    const extensionOf = (name) => {
        const parts = (name || '').split('.');

        return parts.length > 1 ? parts.pop().toLowerCase() : '';
    };

    const closeDropdowns = () => {
        document.querySelectorAll('[data-dropdown-menu]:not(.hidden)').forEach((menu) => {
            menu.classList.add('hidden');
            menu.previousElementSibling?.setAttribute('aria-expanded', 'false');
        });
    };

    const close = () => {
        const modal = element();

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        modal.querySelector('[data-preview-image]')?.removeAttribute('src');
        modal.querySelector('[data-preview-frame]')?.removeAttribute('src');
    };

    const open = ({ url, name = '', mime = '', size = '' }) => {
        const modal = element();

        if (!modal || !url) {
            return;
        }

        closeDropdowns();

        const extension = extensionOf(name);
        const image = modal.querySelector('[data-preview-image]');
        const frame = modal.querySelector('[data-preview-frame]');
        const text = modal.querySelector('[data-preview-text]');
        const fallback = modal.querySelector('[data-preview-fallback]');

        [image, frame, text, fallback].forEach((node) => node.classList.add('hidden'));
        image.removeAttribute('src');
        frame.removeAttribute('src');
        text.textContent = '';

        modal.querySelector('[data-preview-title]').textContent = name;
        modal.querySelector('[data-preview-meta]').textContent = size;
        modal.querySelector('[data-preview-download]').href = url;

        if (mime.startsWith('image/') || IMAGE_EXTENSIONS.includes(extension)) {
            image.src = url;
            image.alt = name;
            image.classList.remove('hidden');
        } else if (mime === 'application/pdf' || extension === 'pdf') {
            frame.src = url;
            frame.classList.remove('hidden');
        } else if (mime.startsWith('text/') || TEXT_EXTENSIONS.includes(extension)) {
            text.classList.remove('hidden');

            fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then((response) => (response.ok ? response.text() : Promise.reject(new Error('Preview unavailable'))))
                .then((content) => {
                    text.textContent = content;
                })
                .catch(() => {
                    text.classList.add('hidden');
                    fallback.classList.remove('hidden');
                });
        } else {
            fallback.classList.remove('hidden');
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    return { open, close };
})();

window.documentPreview = DocumentPreview;

document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    const trigger = event.target.closest('[data-preview-open]');

    if (!trigger) {
        return;
    }

    event.preventDefault();

    DocumentPreview.open({
        url: trigger.dataset.previewUrl,
        name: trigger.dataset.previewName,
        mime: trigger.dataset.previewMime,
        size: trigger.dataset.previewSize,
    });
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('#preview-modal [data-modal-close]')) {
        return;
    }

    DocumentPreview.close();
});

document.addEventListener('keydown', (event) => {
    const modal = document.getElementById('preview-modal');

    if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
        DocumentPreview.close();
    }
});

window.documentPicker = window.documentPicker || function (options = {}) {
    return {
        files: [],
        dragging: false,
        error: '',
        uploading: false,
        maxFiles: options.maxFiles ?? 10,
        maxSize: options.maxSize ?? 100 * 1024 * 1024,

        startUpload(event) {
            if (this.uploading) {
                event.preventDefault();

                return;
            }

            this.uploading = true;
        },

        handleSelect(event) {
            this.addFiles(event.target.files);
        },

        addFiles(list) {
            this.error = '';

            Array.from(list ?? []).forEach((file) => {
                if (this.files.length >= this.maxFiles) {
                    this.error = 'Você pode anexar no máximo ' + this.maxFiles + ' arquivos.';
                    return;
                }

                if (file.size > this.maxSize) {
                    this.error = file.name + ' ultrapassa o limite de ' + this.formatSize(this.maxSize) + '.';
                    return;
                }

                if (this.files.some((item) => item.name === file.name && item.size === file.size)) {
                    return;
                }

                this.files.push({
                    key: file.name + '-' + file.size + '-' + this.files.length,
                    name: file.name,
                    size: file.size,
                    file: file,
                });
            });

            this.sync();
        },

        removeFile(key) {
            const index = this.files.findIndex((item) => item.key === key);

            if (index === -1) {
                return;
            }

            this.files.splice(index, 1);
            this.sync();
        },

        sync() {
            const transfer = new DataTransfer();

            this.files.forEach((item) => transfer.items.add(item.file));

            this.$refs.documents.files = transfer.files;
        },

        onDragOver(event) {
            if (! this.hasFiles(event)) {
                return;
            }

            event.preventDefault();
            this.dragging = true;
        },

        onDragLeave(event) {
            if (! event.currentTarget.contains(event.relatedTarget)) {
                this.dragging = false;
            }
        },

        handleDrop(event) {
            this.dragging = false;

            if (! this.hasFiles(event)) {
                return;
            }

            event.preventDefault();
            this.addFiles(event.dataTransfer?.files);
        },

        hasFiles(event) {
            return Array.from(event.dataTransfer?.types ?? []).includes('Files');
        },

        formatSize(bytes) {
            if (bytes < 1024) {
                return bytes + ' B';
            }

            if (bytes < 1024 * 1024) {
                return Math.round(bytes / 1024) + ' KB';
            }

            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },
    };
};

const PasswordVisibility = (() => {
    const toggle = (button) => {
        const input = document.getElementById(button.dataset.passwordToggle);

        if (!input) {
            return;
        }

        const isPassword = input.type === 'password';

        input.type = isPassword ? 'text' : 'password';
        button.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
        button.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');

        const iconToHide = isPassword ? 'visible' : 'hidden';

        button.querySelectorAll('[data-password-icon]').forEach((icon) => {
            icon.classList.toggle('hidden', icon.dataset.passwordIcon === iconToHide);
        });
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-password-toggle]');

        if (!button) {
            return;
        }

        event.preventDefault();
        toggle(button);
    });

    return { toggle };
})();

const LoginForm = (() => {
    const isEmailValid = (field) => field.value.trim() !== '' && field.checkValidity();

    const revealPassword = (form) => {
        const step = form.querySelector('[data-password-step]');

        if (!step || !step.hasAttribute('hidden')) {
            return;
        }

        step.removeAttribute('hidden');
        step.classList.add('auth-reveal');
    };

    const sync = (form) => {
        const button = form.querySelector('[data-login-submit]');
        const email = form.querySelector('#email');
        const password = form.querySelector('#password');
        const step = form.querySelector('[data-password-step]');

        if (!button || !email) {
            return;
        }

        const passwordVisible = step ? !step.hasAttribute('hidden') : true;
        const passwordFilled = !password || password.value.trim() !== '';

        button.disabled = !isEmailValid(email) || (passwordVisible && !passwordFilled);
    };

    const update = (form) => {
        const email = form.querySelector('#email');
        const password = form.querySelector('#password');
        const step = form.querySelector('[data-password-step]');

        if (!email || !step) {
            sync(form);

            return;
        }

        if (isEmailValid(email)) {
            revealPassword(form);
        } else if (email.value.trim() === '') {
            step.setAttribute('hidden', '');
            step.classList.remove('auth-reveal');

            if (password) {
                password.value = '';
            }
        }

        sync(form);
    };

    document.addEventListener('input', (event) => {
        const form = event.target.closest('[data-login-form]');

        if (form) {
            update(form);
        }
    });

    document.addEventListener('change', (event) => {
        const form = event.target.closest('[data-login-form]');

        if (form) {
            update(form);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || event.target.id !== 'email') {
            return;
        }

        const form = event.target.closest('[data-login-form]');
        const step = form ? form.querySelector('[data-password-step]') : null;

        if (!step || step.hasAttribute('hidden')) {
            return;
        }

        event.preventDefault();
        form.querySelector('#password')?.focus();
    });

    document.addEventListener('submit', (event) => {
        if (!event.target.matches('[data-login-form]')) {
            return;
        }

        const step = event.target.querySelector('[data-password-step]');

        if (step && step.hasAttribute('hidden')) {
            event.preventDefault();
            update(event.target);

            return;
        }

        sync(event.target);

        const button = event.target.querySelector('[data-login-submit]');

        if (button) {
            button.disabled = true;
        }
    });

    document.querySelectorAll('[data-login-form]').forEach(update);

    return { sync, update };
})();

// Public forms show how many questions are approved while the response is
// reviewed. The totals are derived from the rendered fields so they keep
// working when the form structure changes.
const FormProgress = (() => {
    const refresh = (form) => {
        const questions = Array.from(form.querySelectorAll('[data-question]'));
        const approved = questions.filter((question) => question.dataset.status === 'aprovado').length;
        const percent = questions.length > 0 ? Math.round((approved / questions.length) * 100) : 0;

        const container = form.querySelector('[data-form-progress]') ?? document.querySelector('[data-form-progress]');

        if (container) {
            const count = container.querySelector('[data-progress-count]');
            const percentage = container.querySelector('[data-progress-percent]');
            const bar = container.querySelector('[data-progress-bar]');

            if (count) {
                count.textContent = `${approved} de ${questions.length}`;
            }

            if (percentage) {
                percentage.textContent = `${percent}%`;
            }

            if (bar) {
                bar.style.width = `${percent}%`;
            }

            container.setAttribute('aria-valuenow', String(percent));
        }

        form.querySelectorAll('[data-form-step]').forEach((step) => {
            const disc = step.querySelector('[data-step-progress]');

            if (!disc) {
                return;
            }

            const stepQuestions = Array.from(step.querySelectorAll('[data-question]'));
            const stepApproved = stepQuestions.filter((question) => question.dataset.status === 'aprovado').length;
            const stepPercent = stepQuestions.length > 0 ? Math.round((stepApproved / stepQuestions.length) * 100) : 0;
            const ring = disc.querySelector('[data-step-progress-ring]');
            const label = disc.querySelector('[data-step-progress-label]');

            disc.setAttribute('aria-valuenow', String(stepPercent));
            disc.setAttribute('aria-valuetext', `${stepApproved} de ${stepQuestions.length} perguntas aprovadas`);

            if (ring) {
                ring.style.strokeDashoffset = String(100 - stepPercent);
            }

            if (label) {
                label.textContent = `${stepPercent}%`;
            }
        });
    };

    document.addEventListener('input', (event) => {
        const form = event.target.closest('[data-form-progress-form]');

        if (form) {
            refresh(form);
        }
    });

    document.addEventListener('change', (event) => {
        const form = event.target.closest('[data-form-progress-form]');

        if (form) {
            refresh(form);
        }
    });

    document.querySelectorAll('[data-form-progress-form]').forEach(refresh);
})();

// Public forms display each section as a step. The sections are rendered in
// the markup so the form works without JavaScript; this module only controls
// which section is visible and validates the current step before advancing.
const FormSteps = (() => {
    document.querySelectorAll('[data-form-steps]').forEach((form) => {
        const steps = Array.from(form.querySelectorAll('[data-form-step]'));

        if (steps.length === 0) {
            return;
        }

        const previous = form.querySelector('[data-form-step-prev]');
        const next = form.querySelector('[data-form-step-next]');
        const submit = form.querySelector('[data-form-step-submit]');
        const currentLabel = form.querySelector('[data-form-step-current]');
        const titleLabel = form.querySelector('[data-form-step-title]');
        const bar = form.querySelector('[data-form-step-bar]');
        const goButtons = Array.from(form.querySelectorAll('[data-form-step-go]'));
        const initial = Number.parseInt(form.dataset.formInitialStep ?? '0', 10);
        let current = Number.isNaN(initial) ? 0 : Math.min(Math.max(initial, 0), steps.length - 1);

        form.noValidate = true;

        const invalidControl = (root) => Array.from(root.querySelectorAll('input, select, textarea'))
            .find((control) => control.willValidate && ! control.checkValidity());

        const titleOf = (index) => steps[index].querySelector('[data-form-step-heading]')?.textContent.trim() ?? '';

        const show = (index, scroll = false) => {
            current = Math.min(Math.max(index, 0), steps.length - 1);

            steps.forEach((step, position) => {
                step.hidden = position !== current;
            });

            if (currentLabel) {
                currentLabel.textContent = String(current + 1);
            }

            if (titleLabel) {
                titleLabel.textContent = titleOf(current);
            }

            if (bar) {
                bar.style.width = `${Math.round(((current + 1) / steps.length) * 100)}%`;
            }

            if (previous) {
                previous.hidden = current === 0;
            }

            if (next) {
                next.hidden = current >= steps.length - 1;
            }

            if (submit) {
                submit.hidden = current < steps.length - 1;
            }

            goButtons.forEach((button, index) => {
                button.dataset.state = index === current ? 'current' : (index < current ? 'complete' : 'upcoming');

                if (index === current) {
                    button.setAttribute('aria-current', 'step');
                } else {
                    button.removeAttribute('aria-current');
                }
            });

            form.dataset.formCurrentStep = String(current);

            if (scroll) {
                steps[current].scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

        previous?.addEventListener('click', () => show(current - 1, true));

        goButtons.forEach((button) => {
            button.addEventListener('click', () => {
                show(Number.parseInt(button.dataset.formStepGo ?? '0', 10), true);
            });
        });

        next?.addEventListener('click', () => {
            const invalid = invalidControl(steps[current]);

            if (invalid) {
                invalid.reportValidity();

                return;
            }

            show(current + 1, true);
        });

        form.addEventListener('submit', (event) => {
            const invalid = invalidControl(form);

            if (! invalid) {
                return;
            }

            const step = invalid.closest('[data-form-step]');

            if (! step) {
                return;
            }

            event.preventDefault();
            show(steps.indexOf(step), true);
            invalid.reportValidity();
        });

        show(current);
    });

    return {};
})();

// Public image fields accept several images from both the gallery and the
// camera. Selections accumulate in the gallery input so choosing another
// image does not replace the previous ones. The camera input stays separate
// as a no-JavaScript fallback; in that case the server merges both uploads.
const ImageSelections = (() => {
    const MAX_IMAGES = 5;
    const keyOf = (file) => `${file.name}-${file.size}-${file.lastModified}`;
    const states = new WeakMap();

    document.querySelectorAll('[data-image-field]').forEach((field) => {
        const gallery = field.querySelector('[data-image-input]');
        const camera = field.querySelector('[data-camera-input]');
        const question = field.closest('[data-question]');

        if (! gallery || ! camera || typeof DataTransfer === 'undefined') {
            return;
        }

        const state = {
            gallery,
            camera,
            question,
            files: Array.from(gallery.files ?? []),
            existing: question?.querySelectorAll('[data-image-existing]').length ?? 0,
            limitReached: false,
            syncing: false,
            limitMessage: question?.querySelector('[data-image-limit-message]'),
        };

        const sync = () => {
            const transfer = new DataTransfer();

            state.files.forEach((file) => transfer.items.add(file));

            try {
                gallery.files = transfer.files;
            } catch {
                return;
            }

            if ((gallery.files?.length ?? 0) !== state.files.length) {
                return;
            }

            state.syncing = true;
            gallery.dispatchEvent(new Event('change', { bubbles: true }));
            state.syncing = false;
        };

        const add = (incoming) => {
            const known = new Set(state.files.map(keyOf));

            incoming.forEach((file) => {
                const key = keyOf(file);

                if (known.has(key)) {
                    return;
                }

                if (state.existing + state.files.length >= MAX_IMAGES) {
                    state.limitReached = true;

                    return;
                }

                state.files.push(file);
                known.add(key);
            });

            if (state.limitMessage) {
                state.limitMessage.hidden = ! state.limitReached;
            }
        };

        state.sync = sync;
        states.set(field, state);

        gallery.addEventListener('change', () => {
            if (state.syncing) {
                return;
            }

            add(Array.from(gallery.files ?? []));
            sync();
        });

        camera.addEventListener('change', () => {
            add(Array.from(camera.files ?? []));
            camera.value = '';
            sync();
        });
    });

    const clear = (field) => {
        const state = states.get(field);

        if (! state) {
            return;
        }

        state.files = [];
        state.limitReached = false;
        state.gallery.value = '';
        state.camera.value = '';

        if (state.limitMessage) {
            state.limitMessage.hidden = true;
        }

        state.syncing = true;
        state.gallery.dispatchEvent(new Event('change', { bubbles: true }));
        state.syncing = false;
    };

    const keysOf = (field) => new Set((states.get(field)?.files ?? []).map(keyOf));

    // Remove the files already stored by an automatic save while keeping any
    // files selected while the request was in flight.
    const remove = (field, keys) => {
        const state = states.get(field);

        if (! state) {
            return;
        }

        const stored = new Set(keys);
        state.files = state.files.filter((file) => ! stored.has(keyOf(file)));
        state.limitReached = false;

        if (state.limitMessage) {
            state.limitMessage.hidden = true;
        }

        state.sync();
    };

    const setExisting = (field, count) => {
        const state = states.get(field);

        if (! state) {
            return;
        }

        state.existing = count;
        state.limitReached = false;

        if (state.limitMessage) {
            state.limitMessage.hidden = true;
        }
    };

    return { clear, keysOf, remove, setExisting };
})();

// Public image fields show a live preview of the selected gallery and camera
// files before the form is submitted. Previews are scaled down and re-encoded
// as small JPEGs, so selecting large photos does not keep full-resolution
// images in memory. The server-rendered empty placeholder stays visible only
// while there is nothing to preview.
const ImagePreviews = (() => {
    const PREVIEW_EDGE = 160;
    const PREVIEW_QUALITY = 0.7;
    const tokens = new WeakMap();

    const compress = (file) => new Promise((resolve) => {
        const source = URL.createObjectURL(file);
        const image = new Image();

        image.onload = () => {
            const scale = Math.min(1, PREVIEW_EDGE / Math.max(image.naturalWidth, image.naturalHeight));
            const width = Math.max(1, Math.round(image.naturalWidth * scale));
            const height = Math.max(1, Math.round(image.naturalHeight * scale));
            const canvas = document.createElement('canvas');

            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d')?.drawImage(image, 0, 0, width, height);

            canvas.toBlob((blob) => {
                if (! blob) {
                    resolve(source);

                    return;
                }

                URL.revokeObjectURL(source);
                resolve(URL.createObjectURL(blob));
            }, 'image/jpeg', PREVIEW_QUALITY);
        };

        image.onerror = () => {
            URL.revokeObjectURL(source);
            resolve(null);
        };

        image.src = source;
    });

    const append = (container, url, alt, fullUrl = null) => {
        const item = document.createElement('div');

        item.dataset.imagePreviewItem = '';
        item.dataset.objectUrl = url;

        if (fullUrl) {
            item.dataset.previewUrl = fullUrl;
        }

        item.className = 'h-20 w-20 overflow-hidden rounded-xl ring-1 ring-gray-200 dark:ring-gray-800';

        const image = document.createElement('img');

        image.src = url;
        image.alt = alt;
        image.className = 'h-full w-full object-cover';

        item.append(image);
        container.append(item);
    };

    const render = async (field) => {
        const container = field.closest('[data-question]')?.querySelector('[data-image-thumbnails]');

        if (! container) {
            return;
        }

        const token = (tokens.get(field) ?? 0) + 1;

        tokens.set(field, token);

        container.querySelectorAll('[data-image-preview-item]').forEach((item) => {
            if (item.dataset.objectUrl) {
                URL.revokeObjectURL(item.dataset.objectUrl);
            }

            if (item.dataset.previewUrl) {
                URL.revokeObjectURL(item.dataset.previewUrl);
            }

            item.remove();
        });

        const files = [
            ...Array.from(field.querySelector('[data-image-input]')?.files ?? []),
            ...Array.from(field.querySelector('[data-camera-input]')?.files ?? []),
        ].filter((file) => file.type.startsWith('image/'));

        const empty = container.querySelector('[data-image-empty]');
        const existing = container.querySelectorAll('[data-image-existing]').length;

        if (empty) {
            empty.hidden = files.length > 0 || existing > 0;
        }

        if (files.length === 0) {
            return;
        }

        const urls = await Promise.all(files.map(compress));

        if (tokens.get(field) !== token) {
            urls.forEach((url) => url && URL.revokeObjectURL(url));

            return;
        }

        urls.forEach((url, index) => {
            if (url) {
                append(container, url, files[index].name, URL.createObjectURL(files[index]));
            }
        });
    };

    document.querySelectorAll('[data-image-field]').forEach((field) => {
        field.querySelector('[data-image-input]')?.addEventListener('change', () => render(field));
        field.querySelector('[data-camera-input]')?.addEventListener('change', () => render(field));

        render(field);
    });

    return { render };
})();

// Public image thumbnails open an in-page preview instead of a new tab.
// Existing images use the full-size URL stored in the link, while local
// selections open the original file picked by the user. The previous/next
// controls only walk through the images of the same field.
const ImageModal = (() => {
    const modal = document.querySelector('[data-image-modal]');

    if (!modal) {
        return { open: () => {}, close: () => {} };
    }

    const image = modal.querySelector('[data-image-modal-image]');
    const title = modal.querySelector('[data-image-modal-title]');
    const counter = modal.querySelector('[data-image-modal-counter]');
    const original = modal.querySelector('[data-image-modal-original]');
    const previous = modal.querySelector('[data-image-modal-prev]');
    const next = modal.querySelector('[data-image-modal-next]');
    const closeButton = modal.querySelector('[data-image-modal-close]');

    let items = [];
    let index = 0;
    let lastFocus = null;

    const info = (item) => {
        const thumbnail = item.querySelector('img');
        const url = item.matches('[data-image-existing]')
            ? item.getAttribute('href')
            : item.dataset.previewUrl || item.dataset.objectUrl || thumbnail?.getAttribute('src');

        return {
            url: url || '',
            caption: thumbnail?.alt || '',
        };
    };

    const isOpen = () => !modal.classList.contains('hidden');

    const render = () => {
        const current = info(items[index]);

        image.src = current.url;
        image.alt = current.caption;
        title.textContent = current.caption;
        counter.textContent = items.length > 1 ? `${index + 1} de ${items.length}` : '';
        original.href = current.url;

        previous.hidden = items.length < 2;
        next.hidden = items.length < 2;
    };

    const move = (step) => {
        if (items.length < 2) {
            return;
        }

        index = (index + step + items.length) % items.length;
        render();
    };

    const open = (item) => {
        const container = item.closest('[data-image-thumbnails]');

        if (!container) {
            return;
        }

        items = Array.from(container.querySelectorAll('[data-image-existing], [data-image-preview-item]'));

        const position = items.indexOf(item);

        if (position < 0) {
            return;
        }

        index = position;
        lastFocus = document.activeElement;
        render();

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        closeButton?.focus();
    };

    const close = () => {
        if (!isOpen()) {
            return;
        }

        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        image.removeAttribute('src');
        items = [];
        lastFocus?.focus?.();
        lastFocus = null;
    };

    document.addEventListener('click', (event) => {
        if (
            event.defaultPrevented
            || event.button !== 0
            || event.metaKey
            || event.ctrlKey
            || event.shiftKey
            || event.altKey
        ) {
            return;
        }

        const trigger = event.target.closest('[data-image-existing], [data-image-preview-item]');

        if (!trigger || !trigger.closest('[data-image-thumbnails]')) {
            return;
        }

        event.preventDefault();
        open(trigger);
    });

    modal.addEventListener('click', (event) => {
        if (event.target.closest('[data-image-modal-close]')) {
            close();

            return;
        }

        if (event.target.closest('[data-image-modal-prev]')) {
            move(-1);

            return;
        }

        if (event.target.closest('[data-image-modal-next]')) {
            move(1);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (!isOpen()) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            close();
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            move(-1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            move(1);
        }
    });

    return { open, close };
})();

// Public image thumbnails can be rebuilt after an automatic save or after an
// image is deleted. The markup mirrors the Blade component so the thumbnail
// keeps its preview modal and its own delete button.
const ImageGallery = (() => {
    const STATUS_BADGES = {
        aprovado: {
            label: 'Aprovado',
            classes: 'bg-green-600 text-white',
            path: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        },
        reprovado: {
            label: 'Reprovado',
            classes: 'bg-red-600 text-white',
            path: 'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        },
        enviado: {
            label: 'Enviado',
            classes: 'bg-blue-600 text-white',
            path: 'M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5',
        },
        pendente: {
            label: 'Pendente',
            classes: 'bg-amber-500 text-white',
            path: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        },
        nao_aplicavel: {
            label: 'Não aplicável',
            classes: 'bg-gray-500 text-white',
            path: 'M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        },
    };

    const statusBadge = (status) => {
        const config = STATUS_BADGES[status];

        if (! config) {
            return null;
        }

        const badge = document.createElement('span');
        badge.className = `pointer-events-none absolute bottom-1 right-1 z-10 inline-flex h-5 w-5 items-center justify-center rounded-full ring-1 ring-white/70 dark:ring-gray-900/70 ${config.classes}`;
        badge.setAttribute('role', 'img');
        badge.setAttribute('aria-label', config.label);
        badge.title = config.label;

        const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        icon.setAttribute('class', 'h-3 w-3');
        icon.setAttribute('fill', 'none');
        icon.setAttribute('viewBox', '0 0 24 24');
        icon.setAttribute('stroke-width', '1.5');
        icon.setAttribute('stroke', 'currentColor');
        icon.setAttribute('aria-hidden', 'true');

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('stroke-linecap', 'round');
        path.setAttribute('stroke-linejoin', 'round');
        path.setAttribute('d', config.path);
        icon.append(path);
        badge.append(icon);

        return badge;
    };

    const create = (url, preview, alt, status = 'enviado') => {
        const wrapper = document.createElement('div');
        wrapper.dataset.imageItem = '';
        wrapper.className = 'group relative h-20 w-20';

        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.rel = 'noopener';
        link.dataset.imageExisting = '';
        link.className = 'block h-full w-full overflow-hidden rounded-xl border border-gray-200 transition active:scale-[0.98] dark:border-gray-800';

        const image = document.createElement('img');
        image.src = preview;
        image.alt = alt;
        image.loading = 'lazy';
        image.className = 'h-full w-full object-cover';

        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.imageRemove = '';
        button.setAttribute('aria-label', 'Excluir imagem');
        button.className = 'absolute right-[-10.5px] top-[-10.5px] z-10 inline-flex h-[21px] w-[21px] items-center justify-center rounded-full border border-red-600 bg-red-600 text-white transition hover:border-red-500 hover:bg-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:border-red-500 dark:bg-red-500 dark:hover:border-red-400 dark:hover:bg-red-400';

        const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        icon.setAttribute('class', 'h-3 w-3');
        icon.setAttribute('fill', 'none');
        icon.setAttribute('viewBox', '0 0 24 24');
        icon.setAttribute('stroke-width', '1.5');
        icon.setAttribute('stroke', 'currentColor');
        icon.setAttribute('aria-hidden', 'true');

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('stroke-linecap', 'round');
        path.setAttribute('stroke-linejoin', 'round');
        path.setAttribute('d', 'M6 18 18 6M6 6l12 12');
        icon.append(path);
        button.append(icon);

        link.append(image);
        wrapper.append(link);

        const badge = statusBadge(status);

        if (badge) {
            wrapper.append(badge);
        }

        wrapper.append(button);

        return wrapper;
    };

    const render = (form, fieldId, images, thumbnails = []) => {
        const question = Array.from(form.querySelectorAll('[data-question]'))
            .find((element) => element.dataset.question === fieldId) ?? null;
        const container = question?.querySelector('[data-image-thumbnails]');

        if (! question || ! container) {
            return null;
        }

        container.querySelectorAll('[data-image-item]').forEach((item) => item.remove());

        (images ?? []).forEach((url, index) => {
            container.append(create(url, (thumbnails ?? [])[index] ?? url, 'Imagem enviada'));
        });

        const empty = container.querySelector('[data-image-empty]');
        const previewing = container.querySelectorAll('[data-image-preview-item]').length > 0;

        if (empty) {
            empty.hidden = (images?.length ?? 0) > 0 || previewing;
        }

        question.dataset.hasUpload = (images?.length ?? 0) > 0 ? '1' : '0';
        question.dataset.status = (images?.length ?? 0) > 0 ? 'enviado' : 'pendente';

        const field = question.querySelector('[data-image-field]');

        if (field) {
            ImageSelections.setExisting(field, images?.length ?? 0);
        }

        return question;
    };

    return { render };
})();

// Public image fields save the new photos automatically while the form is
// filled. The request keeps a draft response on the server, so leaving the
// page does not discard the images. The stored URLs then replace the local
// previews and the file inputs are cleared to avoid uploading them twice.
const DraftAutosave = (() => {
    const DEBOUNCE_MS = 400;
    const STATUS_CLASSES = {
        saving: ['text-gray-500', 'dark:text-gray-400'],
        saved: ['text-emerald-600', 'dark:text-emerald-400'],
        error: ['text-red-600', 'dark:text-red-400'],
    };

    const setStatus = (status, state, message) => {
        if (! status) {
            return;
        }

        status.hidden = false;
        status.textContent = message;
        status.classList.remove(
            'text-gray-500', 'text-emerald-600', 'text-red-600',
            'dark:text-gray-400', 'dark:text-emerald-400', 'dark:text-red-400',
        );
        status.classList.add(...STATUS_CLASSES[state]);
    };

    const storedImages = (form, images, thumbnails) => {
        Object.entries(images).forEach(([fieldId, urls]) => {
            ImageGallery.render(form, fieldId, urls, thumbnails[fieldId] ?? []);
        });
    };

    const payload = (form) => {
        const data = new FormData(form);

        form.querySelectorAll('input[type="file"]').forEach((input) => {
            if (! input.name || input.matches('[data-image-input], [data-camera-input]')) {
                return;
            }

            data.delete(input.name);
        });

        return data;
    };

    document.querySelectorAll('form[data-form-autosave-url]').forEach((form) => {
        const url = form.dataset.formAutosaveUrl;
        const status = form.querySelector('[data-draft-autosave-status]');
        let timer = null;
        let inFlight = null;
        let pending = false;
        let submitting = false;

        const selected = () => {
            const fields = new Map();

            form.querySelectorAll('[data-image-field]').forEach((field) => {
                const keys = ImageSelections.keysOf(field);

                if (keys.size > 0) {
                    fields.set(field, keys);
                }
            });

            return fields;
        };

        const schedule = () => {
            clearTimeout(timer);
            timer = setTimeout(save, DEBOUNCE_MS);
        };

        const save = () => {
            clearTimeout(timer);

            if (inFlight) {
                pending = true;

                return inFlight;
            }

            const sent = selected();

            if (sent.size === 0) {
                return Promise.resolve(true);
            }

            pending = false;
            setStatus(status, 'saving', 'Salvando imagens...');

            inFlight = (async () => {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: payload(form),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (! response.ok) {
                        throw new Error(data.message || 'Não foi possível salvar as imagens.');
                    }

                    storedImages(form, data.images || {}, data.thumbnails || {});

                    sent.forEach((keys, field) => ImageSelections.remove(field, keys));

                    setStatus(status, 'saved', data.message || 'Imagens salvas automaticamente.');

                    return true;
                } catch (error) {
                    setStatus(status, 'error', error.message || 'Não foi possível salvar as imagens.');

                    return false;
                } finally {
                    inFlight = null;

                    if (pending) {
                        pending = false;
                        schedule();
                    }
                }
            })();

            return inFlight;
        };

        // Wait for any automatic save to finish before the response is sent, so
        // the same photo is not uploaded twice by simultaneous requests.
        const flush = async () => {
            clearTimeout(timer);

            while (inFlight) {
                await inFlight;
            }

            while (selected().size > 0) {
                const saved = await save();

                while (inFlight) {
                    await inFlight;
                }

                if (! saved) {
                    return;
                }
            }
        };

        const onSubmit = (event) => {
            if (submitting) {
                event.preventDefault();

                return;
            }

            if (! inFlight && selected().size === 0) {
                return;
            }

            event.preventDefault();
            submitting = true;

            flush().finally(() => {
                submitting = false;
                form.removeEventListener('submit', onSubmit);
                form.requestSubmit();
                form.addEventListener('submit', onSubmit);
            });
        };

        form.addEventListener('submit', onSubmit);

        form.addEventListener('change', (event) => {
            if (! event.target.matches('[data-image-input], [data-camera-input]')) {
                return;
            }

            if ((event.target.files?.length ?? 0) === 0) {
                return;
            }

            schedule();
        });
    });

    return {};
})();

// Public image and file fields allow the respondent to delete an upload
// already stored on the server. Deletions share the same draft status feedback.
const setDraftStatus = (form, state, message) => {
    const status = form.querySelector('[data-draft-autosave-status]');

    if (! status) {
        return;
    }

    status.hidden = false;
    status.textContent = message;
    status.classList.remove(
        'text-gray-500', 'text-emerald-600', 'text-red-600',
        'dark:text-gray-400', 'dark:text-emerald-400', 'dark:text-red-400',
    );
    status.classList.add(
        ...(state === 'error'
            ? ['text-red-600', 'dark:text-red-400']
            : ['text-emerald-600', 'dark:text-emerald-400']),
    );
};

// Public image fields allow the respondent to delete a photo already stored
// on the server. The signed endpoint removes it from the response and disk,
// then the gallery is rebuilt from the URLs returned by the server so the
// remaining delete buttons keep pointing at the right image.
const ImageDeletion = (() => {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-image-remove]');

        if (! button) {
            return;
        }

        const form = button.closest('form[data-form-image-delete-url]');
        const question = button.closest('[data-question]');
        const item = button.closest('[data-image-item]');
        const container = question?.querySelector('[data-image-thumbnails]');
        const fieldId = question?.dataset.question;
        const url = form?.dataset.formImageDeleteUrl;

        if (! form || ! question || ! item || ! container || ! fieldId || ! url) {
            return;
        }

        event.preventDefault();

        if (button.disabled) {
            return;
        }

        const anchors = Array.from(container.querySelectorAll('[data-image-existing]'));
        const index = anchors.indexOf(item.querySelector('[data-image-existing]'));

        if (index < 0) {
            return;
        }

        if (! await ConfirmModal.open({
            title: 'Excluir imagem?',
            message: 'A imagem será removida desta resposta. Essa ação não pode ser desfeita.',
            confirmLabel: 'Excluir',
        })) {
            return;
        }

        const data = new FormData();

        data.append('_token', form.querySelector('input[name="_token"]')?.value ?? '');
        data.append('field', fieldId);
        data.append('index', String(index));

        button.disabled = true;
        button.classList.add('opacity-50');

        try {
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: data,
            });

            const payload = await response.json().catch(() => ({}));

            if (! response.ok) {
                throw new Error(payload.message || 'Não foi possível excluir a imagem.');
            }

            const refreshed = ImageGallery.render(
                form,
                fieldId,
                payload.images ?? [],
                payload.thumbnails ?? [],
            );

            refreshed?.dispatchEvent(new Event('change', { bubbles: true }));

            setDraftStatus(form, 'saved', payload.message || 'Imagem excluída.');
        } catch (error) {
            setDraftStatus(form, 'error', error.message || 'Não foi possível excluir a imagem.');
            button.disabled = false;
            button.classList.remove('opacity-50');
        }
    });
})();

// Public file fields expose a delete button for each stored attachment. The
// signed endpoint removes the file from the response and disk, and the row is
// removed from the list without a page reload.
const FileDeletion = (() => {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-file-remove]');

        if (! button) {
            return;
        }

        const form = button.closest('form[data-form-file-delete-url]');
        const question = button.closest('[data-question]');
        const item = button.closest('[data-file-item]');
        const list = question?.querySelector('[data-file-list]');
        const fieldId = question?.dataset.question;
        const url = form?.dataset.formFileDeleteUrl;

        if (! form || ! question || ! item || ! list || ! fieldId || ! url) {
            return;
        }

        event.preventDefault();

        if (button.disabled) {
            return;
        }

        const anchors = Array.from(list.querySelectorAll('[data-file-existing]'));
        const index = anchors.indexOf(item.querySelector('[data-file-existing]'));

        if (index < 0) {
            return;
        }

        if (! await ConfirmModal.open({
            title: 'Excluir arquivo?',
            message: 'O arquivo será removido desta resposta. Essa ação não pode ser desfeita.',
            confirmLabel: 'Excluir',
        })) {
            return;
        }

        const data = new FormData();

        data.append('_token', form.querySelector('input[name="_token"]')?.value ?? '');
        data.append('field', fieldId);
        data.append('index', String(index));

        button.disabled = true;
        button.classList.add('opacity-50');

        try {
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: data,
            });

            const payload = await response.json().catch(() => ({}));

            if (! response.ok) {
                throw new Error(payload.message || 'Não foi possível excluir o arquivo.');
            }

            item.remove();

            question.dataset.hasUpload = list.querySelectorAll('[data-file-existing]').length > 0 ? '1' : '0';
            question.dataset.status = payload.has_upload ? 'enviado' : 'pendente';
            question.dispatchEvent(new Event('change', { bubbles: true }));

            setDraftStatus(form, 'saved', payload.message || 'Arquivo excluído.');
        } catch (error) {
            setDraftStatus(form, 'error', error.message || 'Não foi possível excluir o arquivo.');
            button.disabled = false;
            button.classList.remove('opacity-50');
        }
    });
})();

