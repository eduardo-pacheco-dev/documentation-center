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
