<script>
    const openModal = (modal) => {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = (modal) => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-modal-open]');

        if (opener) {
            event.preventDefault();

            if (typeof closeDropdowns === 'function') {
                closeDropdowns();
            }

            const modal = document.getElementById(opener.dataset.modalOpen);

            if (modal) {
                modal.resetWizard?.();
                openModal(modal);
            }

            return;
        }

        const closer = event.target.closest('[data-modal-close]');

        if (closer) {
            closeModal(closer.closest('[role="dialog"]'));
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
        }
    });

    const progressWidths = ['w-1/3', 'w-2/3', 'w-full'];
    const defaultWizardLabels = ['Identificação', 'Valores', 'Detalhes'];

    const setupWizard = (modal) => {
        const wizard = modal?.querySelector('[data-wizard]');

        if (!wizard || wizard.dataset.wizardReady === 'true') {
            return;
        }

        wizard.dataset.wizardReady = 'true';

        const steps = Array.from(wizard.querySelectorAll('[data-wizard-step]'));

        if (steps.length === 0) {
            return;
        }

        const prevButton = modal.querySelector('[data-wizard-prev]');
        const nextButton = modal.querySelector('[data-wizard-next]');
        const submitButton = modal.querySelector('[data-wizard-submit]');
        const label = modal.querySelector('[data-wizard-label]');
        const counter = modal.querySelector('[data-wizard-counter]');
        const progressBar = modal.querySelector('[data-wizard-progress]');
        const labels = JSON.parse(wizard.dataset.wizardLabels || JSON.stringify(defaultWizardLabels));
        let current = 0;

        const render = () => {
            steps.forEach((step, index) => step.classList.toggle('hidden', index !== current));

            if (label) {
                label.textContent = labels[current] || '';
            }

            if (counter) {
                counter.textContent = 'Passo ' + (current + 1) + ' de ' + steps.length;
            }

            if (progressBar) {
                progressBar.classList.remove(...progressWidths);
                progressBar.classList.add(progressWidths[current] || 'w-full');
            }

            prevButton?.classList.toggle('hidden', current === 0);
            nextButton?.classList.toggle('hidden', current === steps.length - 1);
            submitButton?.classList.toggle('hidden', current !== steps.length - 1);
            submitButton?.classList.toggle('inline-flex', current === steps.length - 1);
        };

        const goTo = (index) => {
            current = Math.max(0, Math.min(index, steps.length - 1));
            render();
        };

        nextButton?.addEventListener('click', () => {
            const step = steps[current];
            const invalid = Array.from(step.querySelectorAll('input, select, textarea')).find((field) => !field.checkValidity());

            if (invalid) {
                invalid.reportValidity();

                return;
            }

            goTo(current + 1);
        });

        prevButton?.addEventListener('click', () => goTo(current - 1));

        const errorStep = () => {
            const index = steps.findIndex((step) => step.querySelector('.text-red-600'));

            return index === -1 ? 0 : index;
        };

        modal.resetWizard = () => goTo(errorStep());

        goTo(errorStep());
    };

    const catalogModal = document.getElementById('catalog-item-modal');
    setupWizard(catalogModal);

    document.querySelectorAll('[data-catalog-edit-modal]').forEach(setupWizard);

    if (catalogModal?.hasAttribute('data-open')) {
        openModal(catalogModal);
    }

    document.querySelectorAll('[data-catalog-edit-modal][data-open]').forEach(openModal);
</script>
