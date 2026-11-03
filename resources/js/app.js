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
