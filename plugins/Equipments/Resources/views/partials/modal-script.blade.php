<script>
    (function () {
        const openModal = (modal) => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            const error = modal.querySelector('[data-modal-error]');

            if (error) {
                error.scrollIntoView({ block: 'center' });
            }
        };

        const closeModal = (modal) => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        const closeDropdowns = (except) => {
            document.querySelectorAll('[data-dropdown-menu]:not(.hidden)').forEach((menu) => {
                if (menu !== except) {
                    menu.classList.add('hidden');
                    menu.previousElementSibling.setAttribute('aria-expanded', 'false');
                }
            });
        };

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-dropdown-toggle]');

            if (toggle) {
                const menu = toggle.nextElementSibling;
                const willOpen = menu.classList.contains('hidden');
                closeDropdowns(willOpen ? menu : null);
                menu.classList.toggle('hidden', !willOpen);
                toggle.setAttribute('aria-expanded', String(willOpen));

                return;
            }

            if (!event.target.closest('[data-dropdown-menu]')) {
                closeDropdowns();
            }

            const opener = event.target.closest('[data-modal-open]');

            if (opener) {
                event.preventDefault();
                closeDropdowns();

                const modal = document.getElementById(opener.dataset.modalOpen);

                if (modal) {
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
                closeDropdowns();
                document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
            }
        });

        document.querySelectorAll('[role="dialog"][data-open]').forEach(openModal);

        const wizard = document.querySelector('[data-wizard]');

        if (wizard) {
            const panels = Array.from(wizard.querySelectorAll('[data-wizard-panel]'));
            const stepsList = wizard.querySelector('[data-wizard-steps]');
            const prevButton = wizard.querySelector('[data-wizard-prev]');
            const nextButton = wizard.querySelector('[data-wizard-next]');
            const submitButton = wizard.querySelector('[data-wizard-submit]');
            const scroller = wizard.closest('.overflow-y-auto');
            const baseIndicator = 'inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition';
            const inactiveIndicator = baseIndicator + ' border-gray-300 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800';
            const activeIndicator = baseIndicator + ' border-indigo-600 bg-indigo-600 text-white shadow-sm';
            const doneIndicator = baseIndicator + ' border-emerald-500 text-emerald-600 dark:text-emerald-400';
            const baseBadge = 'inline-flex h-5 w-5 items-center justify-center rounded-full text-[0.65rem] font-semibold';
            const inactiveBadge = baseBadge + ' bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200';
            const activeBadge = baseBadge + ' bg-white/25 text-white';
            const doneBadge = baseBadge + ' bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300';
            let current = 0;

            if (stepsList) {
                stepsList.innerHTML = panels.map((panel, index) => `
                    <li>
                        <button type="button" data-wizard-indicator="${index}" class="${inactiveIndicator}">
                            <span data-wizard-badge class="${inactiveBadge}">${index + 1}</span>
                            <span>${panel.dataset.wizardTitle ?? ''}</span>
                        </button>
                    </li>
                `).join('');
            }

            const indicators = stepsList ? Array.from(stepsList.querySelectorAll('[data-wizard-indicator]')) : [];

            const firstInvalid = (panel) => Array.from(panel.querySelectorAll('input, select, textarea'))
                .find((field) => !field.checkValidity());

            const fieldValue = (name) => {
                const field = wizard.querySelector(`[name="${name}"]`);

                if (!field) {
                    return '';
                }

                return field.tagName === 'SELECT'
                    ? (field.options[field.selectedIndex]?.text ?? '').trim()
                    : field.value.trim();
            };

            const updateReview = () => {
                wizard.querySelectorAll('[data-review-field]').forEach((target) => {
                    target.textContent = fieldValue(target.dataset.reviewField) || '—';
                });
            };

            const render = () => {
                updateReview();

                panels.forEach((panel, index) => panel.classList.toggle('hidden', index !== current));

                indicators.forEach((indicator, index) => {
                    indicator.className = index === current
                        ? activeIndicator
                        : (index < current ? doneIndicator : inactiveIndicator);

                    const badge = indicator.querySelector('[data-wizard-badge]');

                    if (badge) {
                        badge.className = index === current
                            ? activeBadge
                            : (index < current ? doneBadge : inactiveBadge);
                    }
                });

                prevButton.classList.toggle('hidden', current === 0);
                nextButton.classList.toggle('hidden', current === panels.length - 1);
                submitButton.classList.toggle('hidden', current !== panels.length - 1);
                submitButton.classList.toggle('inline-flex', current === panels.length - 1);
            };

            const goTo = (index) => {
                current = Math.max(0, Math.min(index, panels.length - 1));
                render();

                if (scroller) {
                    scroller.scrollTop = 0;
                }
            };

            nextButton.addEventListener('click', () => {
                const invalid = firstInvalid(panels[current]);

                if (invalid) {
                    invalid.reportValidity();
                    return;
                }

                goTo(current + 1);
            });

            prevButton.addEventListener('click', () => goTo(current - 1));

            indicators.forEach((indicator) => {
                indicator.addEventListener('click', () => goTo(Number(indicator.dataset.wizardIndicator)));
            });

            wizard.addEventListener('submit', (event) => {
                for (let index = 0; index < panels.length; index++) {
                    const invalid = firstInvalid(panels[index]);

                    if (invalid) {
                        event.preventDefault();
                        goTo(index);
                        invalid.reportValidity();
                        return;
                    }
                }
            });

            wizard.addEventListener('input', updateReview);
            wizard.addEventListener('change', updateReview);

            document.querySelectorAll('[data-modal-open="equipment-modal"]').forEach((opener) => {
                opener.addEventListener('click', () => goTo(0));
            });

            const errorPanel = panels.findIndex((panel) => panel.querySelector('.text-red-600'));

            goTo(errorPanel > -1 ? errorPanel : 0);
        }

        const searchForm = document.querySelector('[data-search-form]');
        const searchInput = document.querySelector('[data-search-input]');

        if (searchForm && searchInput) {
            let searchTimer;

            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => searchForm.submit(), 400);
            });

            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    clearTimeout(searchTimer);
                }
            });

            if (searchInput.value) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }
        }

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('equipments-view', link.dataset.viewToggle);
            });
        });

        (function () {
            const params = new URLSearchParams(window.location.search);

            if (params.has('view')) {
                return;
            }

            const savedView = localStorage.getItem('equipments-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();
    })();
</script>
