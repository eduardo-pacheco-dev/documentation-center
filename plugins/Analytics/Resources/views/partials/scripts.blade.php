<script src="https://cdn.jsdelivr.net/npm/apexcharts@4/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

<script>
    (function () {
        const series = @json($series);
        const workOrderStatuses = @json($workOrderStatuses);
        const tasksByList = @json($tasksByList);
        const formStatuses = @json($formStatuses);
        const snapshotSeries = @json($snapshotSeries);
        const recordsByStage = @json($recordsByStage);
        const aflByStatus = @json($aflByStatus);

        const isDark = document.documentElement.classList.contains('dark');
        const labelColor = isDark ? '#d1d5db' : '#6b7280';
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        const rendered = new Set();

        const baseOptions = {
            chart: {
                height: '100%',
                width: '100%',
                fontFamily: 'inherit',
                foreColor: labelColor,
                toolbar: { show: false },
                animations: { enabled: false },
            },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            dataLabels: { enabled: false },
            tooltip: { theme: isDark ? 'dark' : 'light' },
        };

        const apexArea = (elementId, datasets, categories, colors) => {
            const element = document.getElementById(elementId);

            if (!element) {
                return;
            }

            new ApexCharts(element, {
                ...baseOptions,
                chart: { ...baseOptions.chart, type: 'area' },
                series: datasets,
                xaxis: {
                    categories,
                    tickAmount: 6,
                    labels: { rotate: 0, hideOverlappingLabels: true },
                },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.04 } },
                colors,
                legend: { position: 'bottom', horizontalAlign: 'left' },
            }).render();
        };

        const chartJs = (elementId, config) => {
            const element = document.getElementById(elementId);

            if (!element) {
                return;
            }

            new Chart(element, config);
        };

        const renderers = {
            overview() {
                if (typeof ApexCharts !== 'undefined') {
                    apexArea('analytics-series', [
                        { name: 'Projetos', data: series.projects },
                        { name: 'Ordens de serviço', data: series.work_orders },
                        { name: 'Documentos', data: series.documents },
                    ], series.labels, ['#6366f1', '#0ea5e9', '#10b981']);
                }

                if (typeof Chart === 'undefined') {
                    return;
                }

                Chart.defaults.color = labelColor;
                Chart.defaults.borderColor = gridColor;
                Chart.defaults.font.family = 'inherit';

                chartJs('analytics-work-orders', {
                    type: 'doughnut',
                    data: {
                        labels: workOrderStatuses.length ? workOrderStatuses.map((row) => row.label) : ['Sem dados'],
                        datasets: [{
                            data: workOrderStatuses.length ? workOrderStatuses.map((row) => row.value) : [1],
                            backgroundColor: workOrderStatuses.length ? workOrderStatuses.map((row) => row.color) : ['#e5e7eb'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '64%',
                        plugins: { legend: { display: false } },
                    },
                });

                chartJs('analytics-tasks-by-list', {
                    type: 'bar',
                    data: {
                        labels: tasksByList.labels,
                        datasets: [{ data: tasksByList.values, backgroundColor: '#6366f1', borderRadius: 6, maxBarThickness: 34 }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                            x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true } },
                        },
                    },
                });

                chartJs('analytics-forms', {
                    type: 'doughnut',
                    data: {
                        labels: formStatuses.length ? formStatuses.map((row) => row.label) : ['Sem dados'],
                        datasets: [{
                            data: formStatuses.length ? formStatuses.map((row) => row.value) : [1],
                            backgroundColor: formStatuses.length ? formStatuses.map((row) => row.color) : ['#e5e7eb'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '64%',
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, padding: 12 } } },
                    },
                });
            },

            fams() {
                if (typeof Chart === 'undefined') {
                    return;
                }

                chartJs('analytics-records-stages', {
                    type: 'bar',
                    data: {
                        labels: recordsByStage.labels,
                        datasets: [{ data: recordsByStage.values, backgroundColor: '#6366f1', borderRadius: 6, maxBarThickness: 30 }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0 } },
                            y: {
                                ticks: {
                                    autoSkip: false,
                                    callback(value) {
                                        const label = this.getLabelForValue(value);
                                        return label.length > 24 ? label.slice(0, 24) + '…' : label;
                                    },
                                },
                            },
                        },
                    },
                });
            },

            afl() {
                if (typeof Chart === 'undefined') {
                    return;
                }

                chartJs('analytics-afl-status', {
                    type: 'bar',
                    data: {
                        labels: aflByStatus.labels,
                        datasets: [{ data: aflByStatus.values, backgroundColor: '#f43f5e', borderRadius: 6, maxBarThickness: 30 }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0 } },
                            y: {
                                ticks: {
                                    autoSkip: false,
                                    callback(value) {
                                        const label = this.getLabelForValue(value);
                                        return label.length > 26 ? label.slice(0, 26) + '…' : label;
                                    },
                                },
                            },
                        },
                    },
                });
            },

            snapshots() {
                if (typeof ApexCharts === 'undefined' || !snapshotSeries || snapshotSeries.labels.length < 2) {
                    return;
                }

                apexArea('analytics-snapshots', [
                    { name: 'Projetos', data: snapshotSeries.projects },
                    { name: 'Ordens de serviço', data: snapshotSeries.work_orders },
                ], snapshotSeries.labels, ['#6366f1', '#0ea5e9']);
            },
        };

        const tabs = [...document.querySelectorAll('[data-tab]')];
        const panels = [...document.querySelectorAll('[data-panel]')];

        const activate = (name, updateHash = true) => {
            tabs.forEach((tab) => {
                const active = tab.dataset.tab === name;
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
                tab.classList.toggle('border-indigo-600', active);
                tab.classList.toggle('text-gray-900', active);
                tab.classList.toggle('dark:text-white', active);
                tab.classList.toggle('border-transparent', !active);
                tab.classList.toggle('text-gray-500', !active);
            });

            panels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.panel !== name));

            if (updateHash) {
                history.replaceState(null, '', '#' + name);
            }

            if (!rendered.has(name)) {
                try {
                    renderers[name]?.();
                } catch (error) {
                    console.error('Analytics: falha ao renderizar a aba ' + name, error);
                }

                rendered.add(name);
            }
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activate(tab.dataset.tab));

            tab.addEventListener('keydown', (event) => {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                    return;
                }

                event.preventDefault();

                const index = tabs.indexOf(tab);
                const next = event.key === 'ArrowRight'
                    ? (index + 1) % tabs.length
                    : (index - 1 + tabs.length) % tabs.length;

                tabs[next].focus();
                activate(tabs[next].dataset.tab);
            });
        });

        const initial = window.location.hash.replace('#', '');
        const names = panels.map((panel) => panel.dataset.panel);

        activate(names.includes(initial) ? initial : 'overview', false);

        const openImportModal = (modal) => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            const error = modal.querySelector('[data-modal-error]');
            if (error) {
                error.scrollIntoView({ block: 'center' });
            }
        };

        const closeImportModal = (modal) => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        document.addEventListener('click', (event) => {
            const opener = event.target.closest('[data-modal-open]');

            if (opener) {
                event.preventDefault();

                const modal = document.getElementById(opener.dataset.modalOpen);
                if (modal) {
                    openImportModal(modal);
                }

                return;
            }

            const closer = event.target.closest('[data-modal-close]');
            if (closer) {
                closeImportModal(closer.closest('[role="dialog"]'));
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeImportModal);
            }
        });

        document.querySelectorAll('[role="dialog"][data-open]').forEach(openImportModal);
    })();
</script>
