import gantt from 'dhtmlx-gantt';
import 'dhtmlx-gantt/codebase/dhtmlxgantt.css';

document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('[data-gantt]');

    if (!container) {
        return;
    }

    const canEdit = container.dataset.canEdit === '1';
    const dataUrl = container.dataset.url;
    const moveUrl = container.dataset.moveUrl;
    const progressUrl = container.dataset.progressUrl;
    const csrf = container.dataset.csrf;

    gantt.config.date_format = '%Y-%m-%d %H:%i';
    gantt.config.row_height = 32;
    gantt.config.bar_height = 22;
    gantt.config.readonly = !canEdit;
    gantt.config.autosize = 'y';
    gantt.config.columns = [
        { name: 'wbs', label: 'WBS', width: 60, tree: false },
        { name: 'text', label: 'Tarefa', tree: true, width: 240 },
        { name: 'start_date', label: 'Início', align: 'center', width: 110 },
        { name: 'duration', label: 'Dias', align: 'center', width: 50 },
        { name: 'resource', label: 'Recursos', width: 160 },
    ];

    try {
        gantt.i18n.setLocale('pt');
    } catch (error) {
        // The bundled locale is optional; the default English one is enough.
    }

    gantt.templates.task_class = (start, end, task) => (task.critical ? 'gantt_task_critical' : '');

    const put = (url, body) => fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });

    gantt.attachEvent('onAfterTaskDrag', (id, mode) => {
        if (!canEdit || (mode !== 'move' && mode !== 'resize')) {
            return true;
        }

        const task = gantt.getTask(id);
        const durationDays = Math.max(0, Math.round(((task.end_date - task.start_date) / 86400000) * 100) / 100);

        put(moveUrl.replace('__TASK__', id), {
            start_date: gantt.date.date_to_str('%Y-%m-%d %H:%i')(task.start_date),
            duration_days: durationDays,
        }).then((response) => {
            if (!response.ok) {
                window.location.reload();

                return;
            }

            window.location.reload();
        });

        return true;
    });

    gantt.attachEvent('onAfterTaskUpdate', (id, task) => {
        if (!canEdit) {
            return true;
        }

        const percent = Math.round(task.progress * 100);

        fetch(progressUrl.replace('__TASK__', id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ percent_complete: percent }),
        }).then((response) => {
            if (response.ok) {
                window.location.reload();
            }
        });

        return true;
    });

    gantt.init(container);

    document.addEventListener('gantt:resize', () => {
        gantt.setSizes();
        gantt.render();
    });

    fetch(dataUrl, { headers: { 'Accept': 'application/json' } })
        .then((response) => response.json())
        .then((data) => gantt.parse(data));
});
