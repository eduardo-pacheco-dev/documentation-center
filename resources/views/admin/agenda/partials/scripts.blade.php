@php
    $agendaRoutes = [
        'store' => route('admin.agenda.events.store'),
        'update' => route('admin.agenda.events.update', ['agendaEvent' => '__ID__']),
        'delete' => route('admin.agenda.events.destroy', ['agendaEvent' => '__ID__']),
        'move' => route('admin.agenda.events.move', ['agendaEvent' => '__ID__']),
        'duplicate' => route('admin.agenda.events.duplicate', ['agendaEvent' => '__ID__']),
        'respond' => route('admin.agenda.events.respond', ['agendaEvent' => '__ID__']),
        'freeBusy' => route('admin.agenda.free-busy'),
        'calendarsIndex' => route('admin.agenda.calendars.index'),
        'today' => now()->toDateString(),
        'hourHeight' => \App\Services\Agenda\AgendaGridBuilder::HOUR_HEIGHT,
        'csrf' => csrf_token(),
    ];
@endphp

<script type="application/json" data-agenda-config>{!! json_encode($agendaRoutes) !!}</script>

<script>
    (() => {
        const configNode = document.querySelector('[data-agenda-config]');

        if (!configNode) {
            return;
        }

        const config = JSON.parse(configNode.textContent);
        const csrfToken = config.csrf;
        const eventModal = document.getElementById('agenda-event-modal');
        const calendarModal = document.getElementById('agenda-calendar-modal');

        if (!eventModal) {
            return;
        }

        const eventForm = eventModal.querySelector('[data-agenda-event-form]');
        const deleteForm = document.getElementById('agenda-event-delete-form');
        const duplicateForm = document.getElementById('agenda-event-duplicate-form');
        const respondForm = eventModal.querySelector('[data-agenda-respond-form]');
        const remindersContainer = eventModal.querySelector('[data-agenda-reminders]');
        const attachmentsContainer = eventModal.querySelector('[data-agenda-attachments]');
        const attendeeList = eventModal.querySelector('[data-agenda-attendee-list]');
        const attendeesInput = eventModal.querySelector('[data-agenda-attendees]');
        const scopeFieldset = eventModal.querySelector('[data-agenda-scope-fieldset]');
        const adminWrap = eventModal.querySelector('[data-agenda-event-admin]');
        const reminderTemplate = document.querySelector('[data-agenda-reminder-template]');
        const attachmentTemplate = document.querySelector('[data-agenda-attachment-template]');
        const frequencySelect = eventModal.querySelector('[data-agenda-field="repeat_frequency"]');
        const endModeSelect = eventModal.querySelector('[data-agenda-field="repeat_end_mode"]');
        const untilWrap = eventModal.querySelector('[data-agenda-repeat-until]');
        const countWrap = eventModal.querySelector('[data-agenda-repeat-count]');
        const weekdaysWrap = eventModal.querySelector('[data-agenda-repeat-weekdays]');

        const statusLabels = { needs_action: 'Sem resposta', declined: 'Não vou', tentative: 'Talvez', accepted: 'Vou' };
        const statusClasses = {
            needs_action: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
            declined: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
            tentative: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            accepted: 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
        };
        const roleLabels = { owner: 'Proprietário', writer: 'Pode editar', reader: 'Pode ver os detalhes', free_busy_reader: 'Vê apenas disponibilidade' };

        let currentEvent = null;
        let reminderIndex = 0;
        let attachmentIndex = 0;
        let dragEndedAt = 0;

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

        const field = (name) => eventModal.querySelector(`[data-agenda-field="${name}"]`);

        const dateTimeValue = (value) => {
            if (!value) {
                return '';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return '';
            }

            const pad = (number) => String(number).padStart(2, '0');

            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        };

        const checkColor = (value) => {
            eventModal.querySelectorAll('[data-agenda-field="color"]').forEach((input) => {
                input.checked = (input.value || '') === (value || '');
            });
        };

        const syncRecurrence = () => {
            const frequency = frequencySelect.value;
            untilWrap.hidden = !(frequency && endModeSelect.value === 'until');
            countWrap.hidden = !(frequency && endModeSelect.value === 'count');
            weekdaysWrap.hidden = frequency !== 'weekly';
        };

        const addReminderRow = (reminder = {}) => {
            const node = reminderTemplate.content.firstElementChild.cloneNode(true);
            node.innerHTML = node.innerHTML.replaceAll('__INDEX__', String(reminderIndex++));
            const [method, minutes] = [node.querySelector('select'), node.querySelector('input')];

            if (reminder.method) {
                method.value = reminder.method;
            }

            if (reminder.minutes_before !== null && reminder.minutes_before !== undefined) {
                minutes.value = reminder.minutes_before;
            }

            node.querySelector('[data-agenda-remove-reminder]').addEventListener('click', () => node.remove());
            remindersContainer.appendChild(node);
        };

        const addAttachmentRow = (attachment = {}) => {
            const node = attachmentTemplate.content.firstElementChild.cloneNode(true);
            node.innerHTML = node.innerHTML.replaceAll('__INDEX__', String(attachmentIndex++));
            const inputs = node.querySelectorAll('input');

            inputs[0].value = attachment.title ?? '';
            inputs[1].value = attachment.url ?? '';
            node.querySelector('[data-agenda-remove-attachment]').addEventListener('click', () => node.remove());
            attachmentsContainer.appendChild(node);
        };

        const renderAttendees = (attendees) => {
            attendeeList.innerHTML = '';

            attendees.forEach((attendee) => {
                const row = document.createElement('div');
                const classes = statusClasses[attendee.status] ?? statusClasses.needs_action;
                const label = statusLabels[attendee.status] ?? statusLabels.needs_action;

                row.className = 'flex items-center justify-between gap-2 rounded-lg bg-gray-50 px-2 py-1 text-xs dark:bg-gray-800/60';
                row.innerHTML = `<span class="truncate text-gray-600 dark:text-gray-300">${attendee.name ? `${attendee.name} · ` : ''}${attendee.email}</span>`
                    + `<span class="shrink-0 rounded-md px-1.5 py-0.5 font-semibold ${classes}">${label}</span>`;
                attendeeList.appendChild(row);
            });
        };

        const syncAttendeeInputs = () => {
            eventForm.querySelectorAll('[data-agenda-attendee-hidden]').forEach((input) => input.remove());

            attendeesInput.value
                .split(/[\s,;]+/)
                .map((email) => email.trim())
                .filter(Boolean)
                .forEach((email) => {
                    const input = document.createElement('input');

                    input.type = 'hidden';
                    input.name = 'attendees[]';
                    input.value = email;
                    input.setAttribute('data-agenda-attendee-hidden', '');
                    eventForm.appendChild(input);
                });
        };

        const fillEventModal = (payload, date) => {
            currentEvent = payload;
            eventForm.reset();
            remindersContainer.innerHTML = '';
            attachmentsContainer.innerHTML = '';
            attendeeList.innerHTML = '';
            reminderIndex = 0;
            attachmentIndex = 0;

            const title = eventModal.querySelector('[data-agenda-event-title]');
            const organizer = eventModal.querySelector('[data-agenda-event-organizer]');
            const method = eventModal.querySelector('[data-agenda-event-method]');

            if (payload) {
                title.textContent = 'Editar evento';
                organizer.textContent = payload.organizer ? `Organizado por ${payload.organizer}` : '';
                eventForm.action = config.update.replace('__ID__', payload.id);
                method.value = 'PUT';

                field('title').value = payload.title ?? '';
                field('description').value = payload.description ?? '';
                field('location').value = payload.location ?? '';
                field('meeting_url').value = payload.meeting_url ?? '';
                field('start_at').value = dateTimeValue(payload.start);
                field('end_at').value = dateTimeValue(payload.end);
                field('is_all_day').checked = Boolean(payload.is_all_day);
                field('status').value = payload.status ?? 'confirmed';
                field('visibility').value = payload.visibility ?? 'default';
                field('transparency').value = payload.transparency ?? 'opaque';
                field('agenda_calendar_id').value = payload.calendar_id ?? field('agenda_calendar_id').value;
                eventModal.querySelector('[data-agenda-recurrence-id]').value = payload.recurrence_id ?? '';
                checkColor(payload.color ?? '');
                field('attendees_can_invite_others').checked = Boolean(payload.attendees_can_invite_others);
                field('attendees_can_modify').checked = Boolean(payload.attendees_can_modify);
                field('attendees_can_see_other_attendees').checked = Boolean(payload.attendees_can_see_other_attendees);

                const recurrence = payload.recurrence ?? {};
                frequencySelect.value = recurrence.frequency ?? '';
                eventModal.querySelector('[data-agenda-field="repeat_interval"]').value = recurrence.interval ?? 1;

                if (recurrence.count) {
                    endModeSelect.value = 'count';
                    field('repeat_count').value = recurrence.count;
                } else if (payload.recurrence_until || recurrence.until) {
                    endModeSelect.value = 'until';
                    field('repeat_until').value = payload.recurrence_until ?? recurrence.until;
                } else {
                    endModeSelect.value = 'never';
                }

                eventModal.querySelectorAll('[data-agenda-weekday]').forEach((input) => {
                    input.checked = (recurrence.byweekday ?? []).includes(Number(input.value));
                });

                (payload.reminders ?? []).forEach(addReminderRow);
                (payload.attachments ?? []).forEach(addAttachmentRow);
                attendeesInput.value = (payload.attendees ?? [])
                    .filter((attendee) => !attendee.is_organizer)
                    .map((attendee) => attendee.email)
                    .join(', ');
                renderAttendees(payload.attendees ?? []);

                scopeFieldset.hidden = !payload.is_recurring;
                eventModal.querySelector('input[name="scope"][value="all"]').checked = true;
                adminWrap.hidden = false;
                eventModal.querySelector('[data-agenda-duplicate]').hidden = !payload.can_update;
                eventModal.querySelector('[data-agenda-delete]').hidden = !payload.can_delete;
                respondForm.action = config.respond.replace('__ID__', payload.id);
                respondForm.classList.toggle('hidden', !payload.can_respond);
            } else {
                title.textContent = 'Novo evento';
                organizer.textContent = '';
                eventForm.action = eventForm.dataset.storeUrl;
                method.value = 'POST';

                const base = date || config.today;
                field('start_at').value = `${base}T09:00`;
                field('end_at').value = `${base}T10:00`;
                field('status').value = 'confirmed';
                field('visibility').value = 'default';
                field('transparency').value = 'opaque';
                field('attendees_can_invite_others').checked = true;
                field('attendees_can_see_other_attendees').checked = true;
                eventModal.querySelector('[data-agenda-recurrence-id]').value = '';
                checkColor('');
                frequencySelect.value = '';
                endModeSelect.value = 'never';
                scopeFieldset.hidden = true;
                adminWrap.hidden = true;
                respondForm.classList.add('hidden');
            }

            syncRecurrence();
        };

        const calendarUrls = calendarModal
            ? JSON.parse(document.querySelector('[data-agenda-calendar-urls]')?.textContent ?? '{}')
            : {};

        const fillCalendarModal = (payload) => {
            if (!calendarModal) {
                return;
            }

            const form = calendarModal.querySelector('[data-agenda-calendar-form]');
            const method = calendarModal.querySelector('[data-agenda-calendar-method]');
            const title = calendarModal.querySelector('[data-agenda-calendar-title]');
            const admin = calendarModal.querySelector('[data-agenda-calendar-admin]');
            const sharesWrap = calendarModal.querySelector('[data-agenda-calendar-shares]');
            const shareList = calendarModal.querySelector('[data-agenda-calendar-share-list]');
            const shareForm = calendarModal.querySelector('[data-agenda-calendar-share-form]');
            const deleteForm = document.getElementById('agenda-calendar-delete-form');
            const defaultForm = document.getElementById('agenda-calendar-default-form');

            form.reset();

            if (payload && payload.id) {
                title.textContent = 'Editar calendário';
                form.action = calendarUrls.update.replace('__ID__', payload.id);
                method.value = 'PUT';
                admin.hidden = !payload.is_owner;
                sharesWrap.hidden = !payload.is_owner;
                deleteForm.action = calendarUrls.delete.replace('__ID__', payload.id);
                defaultForm.action = calendarUrls.default.replace('__ID__', payload.id);
                shareForm.action = calendarUrls.share.replace('__ID__', payload.id);

                calendarModal.querySelector('[data-agenda-calendar-field="name"]').value = payload.name ?? '';
                calendarModal.querySelector('[data-agenda-calendar-field="description"]').value = payload.description ?? '';
                calendarModal.querySelector('[data-agenda-calendar-field="timezone"]').value = payload.timezone ?? 'UTC';
                calendarModal.querySelectorAll('[data-agenda-calendar-color]').forEach((input) => {
                    input.checked = input.value === (payload.color ?? '');
                });

                const reminder = (payload.default_reminders ?? [])[0];
                calendarModal.querySelector('[name="default_reminders[0][method]"]').value = reminder?.method ?? 'popup';
                calendarModal.querySelector('[name="default_reminders[0][minutes_before]"]').value = reminder?.minutes_before ?? '';

                shareList.innerHTML = '';
                (payload.shares ?? []).forEach((share) => {
                    const row = document.createElement('div');
                    const removeForm = document.createElement('form');

                    row.className = 'flex items-center justify-between gap-2 rounded-lg bg-gray-50 px-2 py-1 text-xs dark:bg-gray-800/60';
                    row.innerHTML = `<span class="truncate text-gray-600 dark:text-gray-300">${share.email ?? ''} · ${roleLabels[share.role] ?? share.role}</span>`;
                    removeForm.method = 'POST';
                    removeForm.action = calendarUrls.unshare.replace('__ID__', payload.id).replace('__SHARE__', share.id);
                    removeForm.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="_method" value="DELETE">`
                        + '<button type="submit" class="rounded-md px-1.5 py-0.5 font-semibold text-gray-400 transition hover:text-red-600">Remover</button>';
                    row.appendChild(removeForm);
                    shareList.appendChild(row);
                });
            } else {
                title.textContent = 'Novo calendário';
                form.action = form.dataset.storeUrl;
                method.value = 'POST';
                admin.hidden = true;
                sharesWrap.hidden = true;
                shareList.innerHTML = '';
                calendarModal.querySelector('[data-agenda-calendar-field="name"]').value = '';
                calendarModal.querySelector('[data-agenda-calendar-field="description"]').value = '';
                calendarModal.querySelectorAll('[data-agenda-calendar-color]').forEach((input) => {
                    input.checked = input.value === 'blueberry';
                });
                calendarModal.querySelector('[name="default_reminders[0][method]"]').value = 'popup';
                calendarModal.querySelector('[name="default_reminders[0][minutes_before]"]').value = '10';
            }
        };

        frequencySelect.addEventListener('change', syncRecurrence);
        endModeSelect.addEventListener('change', syncRecurrence);
        eventModal.querySelector('[data-agenda-add-reminder]').addEventListener('click', () => addReminderRow({ method: 'popup', minutes_before: 10 }));
        eventModal.querySelector('[data-agenda-add-attachment]').addEventListener('click', () => addAttachmentRow());
        eventForm.addEventListener('submit', syncAttendeeInputs);

        const allDay = field('is_all_day');

        allDay.addEventListener('change', () => {
            const start = field('start_at');
            const end = field('end_at');

            if (allDay.checked) {
                start.value = `${(start.value || `${config.today}T09:00`).slice(0, 10)}T00:00`;
                end.value = `${(end.value || `${config.today}T10:00`).slice(0, 10)}T00:00`;
            } else {
                if (start.value.endsWith('T00:00')) {
                    start.value = `${start.value.slice(0, 10)}T09:00`;
                }

                if (end.value.endsWith('T00:00')) {
                    end.value = `${end.value.slice(0, 10)}T10:00`;
                }
            }
        });

        document.addEventListener('click', (event) => {
            const createButton = event.target.closest('[data-agenda-create]');

            if (createButton) {
                event.preventDefault();
                fillEventModal(null, createButton.dataset.date);
                openModal(eventModal);

                return;
            }

            const eventButton = event.target.closest('[data-agenda-event]');

            if (eventButton) {
                event.preventDefault();

                if (Date.now() - dragEndedAt < 300) {
                    return;
                }

                fillEventModal(JSON.parse(eventButton.dataset.event));
                openModal(eventModal);

                return;
            }

            const calendarCreate = event.target.closest('[data-agenda-calendar-create]');

            if (calendarCreate) {
                event.preventDefault();
                fillCalendarModal(null);
                openModal(calendarModal);

                return;
            }

            const calendarEdit = event.target.closest('[data-agenda-calendar-edit]');

            if (calendarEdit) {
                event.preventDefault();
                fillCalendarModal(JSON.parse(calendarEdit.dataset.calendar));
                openModal(calendarModal);

                return;
            }

            const closer = event.target.closest('[data-modal-close]');

            if (closer) {
                closeModal(closer.closest('[role="dialog"]'));
            }
        });

        eventModal.querySelector('[data-agenda-duplicate]').addEventListener('click', () => {
            if (!currentEvent) {
                return;
            }

            duplicateForm.action = config.duplicate.replace('__ID__', currentEvent.id);
            duplicateForm.submit();
        });

        eventModal.querySelector('[data-agenda-delete]').addEventListener('click', () => {
            if (!currentEvent || !window.confirm('Excluir este evento?')) {
                return;
            }

            const scope = eventForm.querySelector('input[name="scope"]:checked')?.value ?? 'all';

            deleteForm.action = config.delete.replace('__ID__', currentEvent.id);
            deleteForm.querySelector('[data-agenda-delete-scope]').value = currentEvent.is_recurring ? scope : 'all';
            deleteForm.querySelector('[data-agenda-delete-recurrence]').value = currentEvent.recurrence_id ?? '';
            deleteForm.submit();
        });

        eventModal.addEventListener('click', (event) => {
            const respondButton = event.target.closest('[data-agenda-respond]');

            if (!respondButton) {
                return;
            }

            respondForm.querySelector('[data-agenda-respond-status]').value = respondButton.dataset.agendaRespond;
            respondForm.submit();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal(eventModal);
                closeModal(calendarModal);
            }
        });

        document.addEventListener('change', (event) => {
            if (!event.target.matches('[data-agenda-calendar-toggle]')) {
                return;
            }

            const current = new URLSearchParams(window.location.search);
            const params = new URLSearchParams();

            ['view', 'date', 'q'].forEach((key) => {
                if (current.get(key)) {
                    params.set(key, current.get(key));
                }
            });

            document.querySelectorAll('[data-agenda-calendar-toggle]:checked').forEach((input) => {
                params.append('calendars[]', input.value);
            });

            window.location.search = params.toString();
        });

        const calendarList = document.querySelector('[data-agenda-calendar-list]');

        if (calendarList) {
            const loadingIndicator = document.querySelector('[data-agenda-calendar-loading]');
            const endIndicator = document.querySelector('[data-agenda-calendar-end]');
            const sentinel = document.querySelector('[data-agenda-calendar-sentinel]');
            const perPage = Number(calendarList.dataset.perPage) || 10;
            let nextPage = Number(calendarList.dataset.nextPage) || 0;
            let lastPage = Number(calendarList.dataset.lastPage) || 1;
            let loading = false;
            let ticking = false;

            const selectedIds = () => new Set(
                Array.from(document.querySelectorAll('[data-agenda-calendar-toggle]:checked')).map((input) => input.value),
            );

            const appendItem = (html, selected) => {
                const template = document.createElement('template');
                template.innerHTML = html.trim();

                const item = template.content.firstElementChild;

                if (!item) {
                    return;
                }

                const checkbox = item.querySelector('[data-agenda-calendar-toggle]');

                if (checkbox) {
                    checkbox.checked = selected.has(checkbox.value);
                }

                calendarList.insertBefore(item, sentinel);
            };

            const showEnd = () => {
                endIndicator?.classList.remove('hidden');
            };

            const loadMore = async () => {
                if (loading || nextPage < 2 || nextPage > lastPage) {
                    return;
                }

                loading = true;
                loadingIndicator?.classList.remove('hidden');
                loadingIndicator?.classList.add('flex');

                try {
                    const url = new URL(config.calendarsIndex, window.location.origin);

                    url.searchParams.set('page', String(nextPage));
                    url.searchParams.set('per_page', String(perPage));

                    const response = await fetch(url, { headers: { Accept: 'application/json' } });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const data = await response.json();
                    const selected = selectedIds();

                    (data.items ?? []).forEach((item) => appendItem(item.html, selected));

                    lastPage = Number(data.last_page) || lastPage;
                    nextPage = Number(data.current_page) + 1;
                } catch (error) {
                    nextPage = 0;

                    if (endIndicator) {
                        endIndicator.textContent = 'Não foi possível carregar mais calendários.';
                    }

                    showEnd();

                    return;
                } finally {
                    loading = false;
                    loadingIndicator?.classList.add('hidden');
                    loadingIndicator?.classList.remove('flex');
                }

                if (nextPage > lastPage) {
                    nextPage = 0;
                    showEnd();

                    return;
                }

                if (isNearBottom()) {
                    loadMore();
                }
            };

            const isNearBottom = () => calendarList.scrollTop + calendarList.clientHeight
                >= calendarList.scrollHeight - 32;

            calendarList.addEventListener('scroll', () => {
                if (ticking) {
                    return;
                }

                ticking = true;
                window.requestAnimationFrame(() => {
                    ticking = false;

                    if (isNearBottom()) {
                        loadMore();
                    }
                });
            }, { passive: true });

            if (nextPage < 2) {
                showEnd();
            } else if (isNearBottom()) {
                loadMore();
            }
        }

        const freeBusyButton = document.querySelector('[data-agenda-freebusy]');

        if (freeBusyButton) {
            freeBusyButton.addEventListener('click', async () => {
                const result = document.querySelector('[data-agenda-freebusy-result]');
                const url = new URL(config.freeBusy, window.location.origin);
                const current = new URLSearchParams(window.location.search);

                if (current.get('date')) {
                    url.searchParams.set('date', current.get('date'));
                }

                current.forEach((value, key) => {
                    if (key === 'calendars[]') {
                        url.searchParams.append('calendars[]', value);
                    }
                });

                result.innerHTML = '<p class="text-gray-400 dark:text-gray-500">Carregando…</p>';

                try {
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    const data = await response.json();
                    const busy = data.busy ?? [];

                    if (!busy.length) {
                        result.innerHTML = '<p class="text-gray-400 dark:text-gray-500">Nenhum horário ocupado.</p>';

                        return;
                    }

                    const grouped = {};

                    busy.forEach((slot) => {
                        const day = slot.start.slice(0, 10);
                        grouped[day] = grouped[day] ?? [];
                        grouped[day].push(slot);
                    });

                    result.innerHTML = Object.entries(grouped).map(([day, slots]) => {
                        const label = new Date(`${day}T00:00`).toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit', month: '2-digit' });
                        const items = slots.map((slot) => {
                            const start = new Date(slot.start).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                            const end = new Date(slot.end).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

                            return `<li class="truncate">${start}–${end} ${slot.title}</li>`;
                        }).join('');

                        return `<div><p class="font-semibold text-gray-500 dark:text-gray-400">${label}</p><ul class="ml-2 list-disc">${items}</ul></div>`;
                    }).join('');
                } catch (error) {
                    result.innerHTML = '<p class="text-red-500">Não foi possível carregar a disponibilidade.</p>';
                }
            });
        }

        const grid = document.querySelector('[data-agenda-grid]');

        if (grid) {
            const hourHeight = config.hourHeight;
            let drag = null;

            const snap = (pixels) => Math.round(((pixels / hourHeight) * 60) / 15) * 15;

            grid.addEventListener('pointerdown', (event) => {
                const block = event.target.closest('[data-agenda-draggable]');

                if (!block || event.button !== 0) {
                    return;
                }

                event.preventDefault();

                drag = {
                    block,
                    mode: event.target.closest('[data-agenda-resize]') ? 'resize' : 'move',
                    startY: event.clientY,
                    top: parseFloat(block.style.top) || 0,
                    height: parseFloat(block.style.height) || hourHeight,
                    payload: JSON.parse(block.dataset.event),
                };

                block.setPointerCapture(event.pointerId);
                document.body.classList.add('select-none');
            });

            grid.addEventListener('pointermove', (event) => {
                if (!drag) {
                    return;
                }

                const delta = snap(event.clientY - drag.startY);

                if (drag.mode === 'resize') {
                    drag.block.style.height = `${Math.max(15, (drag.height / hourHeight) * 60 + delta) / 60 * hourHeight}px`;
                } else {
                    drag.block.style.top = `${(drag.top / hourHeight) * 60 + delta}px`;
                }
            });

            const finish = async (event) => {
                if (!drag) {
                    return;
                }

                const current = drag;
                drag = null;
                document.body.classList.remove('select-none');

                const deltaMinutes = snap(event.clientY - current.startY);
                let dayDelta = 0;

                if (current.mode === 'move') {
                    const element = document.elementFromPoint(event.clientX, event.clientY);
                    const column = element?.closest('[data-agenda-day-column]');
                    const origin = current.block.closest('[data-agenda-day-column]');

                    if (column && origin && column !== origin) {
                        const columns = Array.from(grid.querySelectorAll('[data-agenda-day-column]'));
                        dayDelta = columns.indexOf(column) - columns.indexOf(origin);
                    }
                }

                if (deltaMinutes === 0 && dayDelta === 0) {
                    current.block.style.top = `${current.top}px`;
                    current.block.style.height = `${current.height}px`;

                    return;
                }

                dragEndedAt = Date.now();

                const start = new Date(new Date(current.payload.start).getTime() + (deltaMinutes + dayDelta * 1440) * 60000);
                const end = new Date(new Date(current.payload.end).getTime() + (deltaMinutes + dayDelta * 1440) * 60000);
                const scope = current.payload.is_recurring
                    ? (window.confirm('Alterar somente esta ocorrência? OK = esta, Cancelar = todas') ? 'this' : 'all')
                    : 'all';

                const pad = (number) => String(number).padStart(2, '0');
                const format = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
                const body = new URLSearchParams({
                    _method: 'PATCH',
                    start_at: format(start),
                    end_at: format(end),
                    scope,
                    recurrence_id: current.payload.recurrence_id ?? '',
                });

                try {
                    await fetch(config.move.replace('__ID__', current.payload.id), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            Accept: 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body,
                    });
                } finally {
                    window.location.reload();
                }
            };

            grid.addEventListener('pointerup', finish);
            grid.addEventListener('pointercancel', () => {
                drag = null;
                document.body.classList.remove('select-none');
            });
        }
    })();
</script>
