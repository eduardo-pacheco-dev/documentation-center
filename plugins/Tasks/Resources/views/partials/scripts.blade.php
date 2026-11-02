<script>
    (() => {
        const openModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = (modal) => {
            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        const taskModal = document.getElementById('task-detail-modal');
        const taskCreateModal = document.getElementById('task-create-modal');
        const listModal = document.getElementById('task-list-modal');
        const stepTemplate = document.querySelector('[data-task-step-template]');

        const formatDate = (date) => {
            const pad = (value) => String(value).padStart(2, '0');

            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
        };

        const setDueQuick = (value) => {
            const input = taskModal.querySelector('[data-task-due]');
            const date = new Date();

            if (value === 'today') {
                input.value = formatDate(date);
            } else if (value === 'tomorrow') {
                date.setDate(date.getDate() + 1);
                input.value = formatDate(date);
            } else if (value === 'next-week') {
                date.setDate(date.getDate() + 7);
                input.value = formatDate(date);
            } else {
                input.value = '';
            }
        };

        const renderSteps = (task) => {
            const container = taskModal.querySelector('[data-task-steps]');

            container.innerHTML = '';

            (task.steps ?? []).forEach((step) => {
                const node = stepTemplate.content.firstElementChild.cloneNode(true);
                const form = node.querySelector('[data-step-form]');

                form.action = step.update_url;
                node.querySelector('[data-step-title]').value = step.title;
                node.querySelector('[data-step-delete-form]').action = step.destroy_url;

                const toggle = node.querySelector('[data-step-toggle]');

                toggle.checked = step.completed;
                toggle.addEventListener('change', () => form.requestSubmit());

                container.appendChild(node);
            });

            taskModal.querySelector('[data-task-steps-count]').textContent = (task.steps ?? []).length;
        };

        const fillTaskModal = (opener) => {
            const raw = opener.dataset.task ?? opener.closest('[data-task]')?.dataset.task;
            const task = raw ? JSON.parse(raw) : {};

            taskModal.querySelector('[data-task-update-form]').action = task.update_url ?? '#';
            taskModal.querySelector('[data-task-delete-form]').action = task.delete_url ?? '#';
            taskModal.querySelector('[data-task-step-form]').action = task.step_store_url ?? '#';
            taskModal.querySelector('[data-task-title]').value = task.title ?? '';
            taskModal.querySelector('[data-task-list]').value = task.list_id ?? '';
            taskModal.querySelector('[data-task-agenda-event]').value = task.agenda_event_id ?? '';
            taskModal.querySelector('[data-task-due]').value = task.due_at ?? '';
            taskModal.querySelector('[data-task-remind]').value = task.remind_at ?? '';
            taskModal.querySelector('[data-task-repeat]').value = task.repeat_type ?? 'none';
            taskModal.querySelector('[data-task-notes]').value = task.notes ?? '';
            taskModal.querySelector('[data-task-important]').checked = Boolean(task.is_important);
            taskModal.querySelector('[data-task-my-day]').checked = Boolean(task.my_day);

            renderSteps(task);
        };

        const checkRadio = (selector, value) => {
            listModal.querySelectorAll(selector).forEach((input) => {
                input.checked = input.value === value;
            });
        };

        const fillListModal = (opener) => {
            const form = listModal.querySelector('[data-list-form]');
            const method = listModal.querySelector('[data-list-method]');
            const title = listModal.querySelector('[data-list-modal-title]');
            const payload = opener.dataset.list ? JSON.parse(opener.dataset.list) : null;

            if (payload) {
                title.textContent = 'Editar lista';
                form.action = payload.update_url;
                method.value = 'PUT';
                listModal.querySelector('[data-list-name]').value = payload.name ?? '';
                checkRadio('[data-list-color]', payload.color ?? 'indigo');
                checkRadio('[data-list-icon]', payload.icon ?? 'check-circle');

                return;
            }

            title.textContent = 'Nova lista';
            form.action = form.dataset.storeUrl;
            method.value = 'POST';
            listModal.querySelector('[data-list-name]').value = '';
            checkRadio('[data-list-color]', 'indigo');
            checkRadio('[data-list-icon]', 'check-circle');
        };

        taskModal.querySelectorAll('[data-task-due-quick]').forEach((button) => {
            button.addEventListener('click', () => setDueQuick(button.dataset.taskDueQuick));
        });

        document.querySelectorAll('[data-auto-submit]').forEach((field) => {
            field.addEventListener('change', () => field.form?.requestSubmit());
        });

        document.addEventListener('click', (event) => {
            const opener = event.target.closest('[data-modal-open]');

            if (opener) {
                event.preventDefault();

                const modal = document.getElementById(opener.dataset.modalOpen);

                if (modal === taskModal) {
                    fillTaskModal(opener);
                } else if (modal === taskCreateModal) {
                    modal.querySelector('[name="title"]')?.focus();
                } else if (modal === listModal) {
                    fillListModal(opener);
                }

                openModal(modal);

                return;
            }

            const closer = event.target.closest('[data-modal-close]');

            if (closer) {
                closeModal(closer.closest('[role="dialog"]'));
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal(taskModal);
                closeModal(taskCreateModal);
                closeModal(listModal);
            }
        });
    })();
</script>
