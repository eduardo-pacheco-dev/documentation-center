<script>
    (() => {
        const container = document.querySelector('[data-gantt]');

        if (!container) {
            return;
        }

        const dayWidth = 28;
        const labelWidth = 260;
        const dayMilliseconds = 86400000;
        const canEdit = container.dataset.canEdit === '1';
        const csrfToken = container.dataset.csrf;
        const dataUrl = container.dataset.url;
        const moveUrl = container.dataset.moveUrl;

        let payload = { data: [], links: [] };
        let rendered = false;

        const parseDate = (value) => {
            if (! value) {
                return null;
            }

            const date = new Date(String(value).replace(' ', 'T'));

            return Number.isNaN(date.getTime()) ? null : date;
        };

        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        };

        const startOfDay = (date) => {
            const copy = new Date(date.getTime());
            copy.setHours(0, 0, 0, 0);

            return copy;
        };

        const emptyState = (message) => {
            container.innerHTML = '';

            const box = document.createElement('p');
            box.className = 'px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400';
            box.textContent = message;
            container.appendChild(box);
        };

        const fetchData = async () => {
            try {
                const response = await fetch(dataUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });

                if (! response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                payload = await response.json();
                render();
            } catch (error) {
                emptyState('Não foi possível carregar o cronograma.');
            }
        };

        const moveTask = async (task, days) => {
            const start = parseDate(task.start_date);

            if (start === null || days === 0) {
                return;
            }

            const newStart = new Date(start.getTime() + days * dayMilliseconds);

            try {
                const response = await fetch(moveUrl.replace('__TASK__', String(task.id)), {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        start_date: `${formatDate(newStart)} 08:00`,
                        duration_days: Math.max(0, Number(task.duration) || 0),
                    }),
                });

                if (response.ok) {
                    await fetchData();
                }
            } catch (error) {
                // Keep the chart as it was when the request fails.
            }
        };

        const makeDraggable = (bar, task) => {
            if (! canEdit || task.type === 'summary') {
                return;
            }

            bar.style.cursor = 'grab';

            bar.addEventListener('pointerdown', (event) => {
                if (event.button !== 0) {
                    return;
                }

                event.preventDefault();
                bar.setPointerCapture(event.pointerId);
                bar.style.cursor = 'grabbing';

                const startX = event.clientX;
                let days = 0;

                const onMove = (moveEvent) => {
                    days = Math.round((moveEvent.clientX - startX) / dayWidth);
                    bar.style.transform = `translateX(${days * dayWidth}px)`;
                };

                const onUp = () => {
                    bar.removeEventListener('pointermove', onMove);
                    bar.removeEventListener('pointerup', onUp);
                    bar.removeEventListener('pointercancel', onUp);
                    bar.style.cursor = 'grab';
                    bar.style.transform = '';

                    moveTask(task, days);
                };

                bar.addEventListener('pointermove', onMove);
                bar.addEventListener('pointerup', onUp);
                bar.addEventListener('pointercancel', onUp);
            });
        };

        const render = () => {
            rendered = true;

            const tasks = Array.isArray(payload.data) ? payload.data : [];
            const starts = tasks.map((task) => parseDate(task.start_date)).filter(Boolean);

            if (tasks.length === 0) {
                emptyState('Nenhuma tarefa no cronograma. Adicione tarefas e recalcule o caminho crítico.');
                return;
            }

            if (starts.length === 0) {
                emptyState('Recalcule o caminho crítico para gerar as datas do cronograma.');
                return;
            }

            const start = startOfDay(new Date(Math.min(...starts.map((date) => date.getTime()))));
            let finish = start;

            tasks.forEach((task) => {
                const taskStart = parseDate(task.start_date);

                if (taskStart === null) {
                    return;
                }

                const duration = Math.max(task.type === 'milestone' ? 0 : 1, Number(task.duration) || 0);
                const taskFinish = new Date(taskStart.getTime() + duration * dayMilliseconds);

                if (taskFinish > finish) {
                    finish = taskFinish;
                }
            });

            const totalDays = Math.min(730, Math.max(1, Math.round((finish - start) / dayMilliseconds) + 1));
            const timelineWidth = totalDays * dayWidth;

            container.innerHTML = '';

            const scroller = document.createElement('div');
            scroller.className = 'overflow-x-auto';

            const inner = document.createElement('div');
            inner.style.minWidth = `${labelWidth + timelineWidth}px`;

            const header = document.createElement('div');
            header.className = 'flex border-b border-gray-200 bg-gray-50 text-xs font-medium text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400';

            const headerLabel = document.createElement('div');
            headerLabel.className = 'sticky left-0 z-10 shrink-0 bg-gray-50 px-4 py-2 dark:bg-gray-800';
            headerLabel.style.width = `${labelWidth}px`;
            headerLabel.textContent = 'Tarefa';

            const headerTimeline = document.createElement('div');
            headerTimeline.className = 'relative flex';
            headerTimeline.style.width = `${timelineWidth}px`;

            for (let index = 0; index < totalDays; index++) {
                const day = new Date(start.getTime() + index * dayMilliseconds);
                const cell = document.createElement('div');
                cell.className = 'flex shrink-0 items-center justify-center border-l border-gray-200 py-2 text-[10px] dark:border-gray-800';
                cell.style.width = `${dayWidth}px`;
                cell.textContent = day.getDate() === 1 || index === 0
                    ? `${String(day.getDate()).padStart(2, '0')}/${String(day.getMonth() + 1).padStart(2, '0')}`
                    : '';
                cell.title = formatDate(day);
                headerTimeline.appendChild(cell);
            }

            header.appendChild(headerLabel);
            header.appendChild(headerTimeline);
            inner.appendChild(header);

            const gridBackground = `repeating-linear-gradient(to right, transparent 0, transparent ${dayWidth - 1}px, rgba(156, 163, 175, 0.25) ${dayWidth - 1}px, rgba(156, 163, 175, 0.25) ${dayWidth}px)`;

            tasks.forEach((task) => {
                const row = document.createElement('div');
                row.className = 'flex border-b border-gray-100 last:border-b-0 dark:border-gray-800/60';

                const label = document.createElement('div');
                label.className = 'sticky left-0 z-10 flex shrink-0 items-center gap-2 bg-white px-4 py-2 text-sm text-gray-700 dark:bg-gray-900 dark:text-gray-200';
                label.style.width = `${labelWidth}px`;

                const depth = String(task.wbs ?? '').split('.').filter(Boolean).length;
                label.style.paddingLeft = `${Math.max(1, depth) * 16}px`;

                if (task.type === 'milestone') {
                    const marker = document.createElement('span');
                    marker.className = 'inline-block h-2 w-2 rotate-45 bg-indigo-500';
                    label.appendChild(marker);
                } else if (task.type === 'summary') {
                    const marker = document.createElement('span');
                    marker.className = 'inline-block h-0.5 w-2.5 bg-gray-400';
                    label.appendChild(marker);
                }

                const text = document.createElement('span');
                text.className = 'truncate';
                text.textContent = task.text;
                text.title = task.text;
                label.appendChild(text);

                if (task.resource) {
                    const resource = document.createElement('span');
                    resource.className = 'truncate text-[10px] text-gray-400 dark:text-gray-500';
                    resource.textContent = task.resource;
                    label.appendChild(resource);
                }

                const timeline = document.createElement('div');
                timeline.className = 'relative shrink-0';
                timeline.style.width = `${timelineWidth}px`;
                timeline.style.height = '36px';
                timeline.style.backgroundImage = gridBackground;

                const taskStart = parseDate(task.start_date);

                if (taskStart !== null) {
                    const offset = Math.max(0, Math.round((startOfDay(taskStart) - start) / dayMilliseconds));
                    const duration = task.type === 'milestone'
                        ? 0
                        : Math.max(1, Number(task.duration) || 0);

                    const bar = document.createElement('div');
                    bar.className = 'absolute top-1/2 flex items-center rounded-md shadow-sm';
                    bar.style.left = `${offset * dayWidth + 2}px`;
                    bar.style.top = '8px';
                    bar.style.height = '20px';
                    bar.style.width = task.type === 'milestone'
                        ? '18px'
                        : `${Math.max(dayWidth - 4, duration * dayWidth - 4)}px`;

                    if (task.type === 'milestone') {
                        bar.style.transform = 'rotate(45deg)';
                        bar.style.borderRadius = '2px';
                        bar.style.background = task.critical ? '#dc2626' : '#6366f1';
                    } else {
                        bar.style.background = task.type === 'summary'
                            ? '#9ca3af'
                            : (task.critical ? '#ef4444' : '#6366f1');

                        if (task.progress > 0 && task.type !== 'summary') {
                            const progress = document.createElement('div');
                            progress.className = 'h-full rounded-l-md bg-emerald-500';
                            progress.style.width = `${Math.min(100, Math.max(0, Number(task.progress) * 100))}%`;
                            bar.appendChild(progress);
                        }
                    }

                    const tooltip = [
                        `${task.wbs ? task.wbs + ' · ' : ''}${task.text}`,
                        task.start_date ? `Início: ${task.start_date}` : null,
                        task.slack ? `Folga: ${task.slack} dia(s)` : null,
                        task.resource ? `Recursos: ${task.resource}` : null,
                    ].filter(Boolean).join('\n');

                    bar.title = tooltip;
                    makeDraggable(bar, task);
                    timeline.appendChild(bar);
                }

                row.appendChild(label);
                row.appendChild(timeline);
                inner.appendChild(row);
            });

            const legend = document.createElement('div');
            legend.className = 'flex flex-wrap items-center gap-4 border-t border-gray-200 px-4 py-2 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400';
            legend.innerHTML = '<span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-5 rounded-sm bg-gray-400"></span> Resumo</span>'
                + '<span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-5 rounded-sm bg-indigo-500"></span> Tarefa</span>'
                + '<span class="inline-flex items-center gap-1.5"><span class="inline-block h-2.5 w-5 rounded-sm bg-red-500"></span> Caminho crítico</span>'
                + '<span class="inline-flex items-center gap-1.5"><span class="inline-block h-2 w-2 rotate-45 bg-indigo-500"></span> Marco</span>';

            inner.appendChild(legend);
            scroller.appendChild(inner);
            container.appendChild(scroller);
        };

        document.addEventListener('gantt:resize', () => {
            if (rendered) {
                render();
            }
        });

        fetchData();
    })();
</script>
