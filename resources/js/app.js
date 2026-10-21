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
    };

    const open = (options = {}) => {
        const modal = element();

        if (!modal) {
            return Promise.resolve(window.confirm(options.message || ''));
        }

        modal.querySelector('[data-confirm-title]').textContent = options.title || 'Tem certeza?';
        modal.querySelector('[data-confirm-message]').textContent = options.message || '';
        modal.querySelector('[data-confirm-accept]').textContent = options.confirmLabel || 'Confirmar';

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

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
    const sync = (form) => {
        const button = form.querySelector('[data-login-submit]');

        if (!button) {
            return;
        }

        const fields = Array.from(form.querySelectorAll('[data-login-required]'));

        button.disabled = fields.some((field) => field.value.trim() === '');
    };

    document.addEventListener('input', (event) => {
        const form = event.target.closest('[data-login-form]');

        if (form) {
            sync(form);
        }
    });

    document.addEventListener('change', (event) => {
        const form = event.target.closest('[data-login-form]');

        if (form) {
            sync(form);
        }
    });

    document.addEventListener('submit', (event) => {
        if (!event.target.matches('[data-login-form]')) {
            return;
        }

        sync(event.target);

        const button = event.target.querySelector('[data-login-submit]');

        if (button) {
            button.disabled = true;
        }
    });

    document.querySelectorAll('[data-login-form]').forEach(sync);

    return { sync };
})();
