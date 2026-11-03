<script src="https://cdn.jsdelivr.net/npm/apexcharts@4/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

<script>
    (function () {
        const series = @json($series);
        const snapshotSeries = @json($snapshotSeries);
        const recordsByStage = @json($recordsByStage);
        const recordsByStatus = @json($recordsByStatus);
        const recordsBySupplier = @json($recordsBySupplier);
        const aflByStatus = @json($aflByStatus);
        const aflByBillingStatus = @json($aflByBillingStatus);
        const aflByProject = @json($aflByProject);
        const nokiaByStatus = @json($nokiaByStatus);

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

        const chartPalette = ['#6366f1', '#f59e0b', '#0ea5e9', '#10b981', '#f43f5e', '#8b5cf6', '#14b8a6', '#eab308', '#64748b', '#ec4899'];
        const chartColors = (count) => Array.from({ length: count }, (_, index) => chartPalette[index % chartPalette.length]);

        const doughnutOptions = () => ({
            responsive: true,
            maintainAspectRatio: false,
            cutout: '64%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, padding: 10 } } },
        });

        const horizontalBarOptions = (maxLabel = 24) => ({
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

                            return label.length > maxLabel ? label.slice(0, maxLabel) + '…' : label;
                        },
                    },
                },
            },
        });

        const renderers = {
            overview() {
                if (typeof ApexCharts !== 'undefined') {
                    apexArea('analytics-series', [
                        { name: 'FAMs', data: series.records },
                        { name: 'AFL', data: series.afl },
                    ], series.labels, ['#6366f1', '#f43f5e']);
                }

                if (typeof Chart === 'undefined') {
                    return;
                }

                Chart.defaults.color = labelColor;
                Chart.defaults.borderColor = gridColor;
                Chart.defaults.font.family = 'inherit';

                chartJs('analytics-overview-records-status', {
                    type: 'doughnut',
                    data: {
                        labels: recordsByStatus.labels.length ? recordsByStatus.labels : ['Sem dados'],
                        datasets: [{
                            data: recordsByStatus.values.length ? recordsByStatus.values : [1],
                            backgroundColor: recordsByStatus.values.length ? chartColors(recordsByStatus.values.length) : ['#e5e7eb'],
                            borderWidth: 0,
                        }],
                    },
                    options: doughnutOptions(),
                });

                chartJs('analytics-overview-records-stages', {
                    type: 'bar',
                    data: {
                        labels: recordsByStage.labels,
                        datasets: [{ data: recordsByStage.values, backgroundColor: '#6366f1', borderRadius: 6, maxBarThickness: 28 }],
                    },
                    options: horizontalBarOptions(24),
                });

                chartJs('analytics-overview-afl-status', {
                    type: 'bar',
                    data: {
                        labels: aflByStatus.labels,
                        datasets: [{ data: aflByStatus.values, backgroundColor: '#f43f5e', borderRadius: 6, maxBarThickness: 28 }],
                    },
                    options: horizontalBarOptions(26),
                });

                chartJs('analytics-overview-afl-billing', {
                    type: 'doughnut',
                    data: {
                        labels: aflByBillingStatus.labels.length ? aflByBillingStatus.labels : ['Sem dados'],
                        datasets: [{
                            data: aflByBillingStatus.values.length ? aflByBillingStatus.values : [1],
                            backgroundColor: aflByBillingStatus.values.length ? chartColors(aflByBillingStatus.values.length) : ['#e5e7eb'],
                            borderWidth: 0,
                        }],
                    },
                    options: doughnutOptions(),
                });

                chartJs('analytics-overview-records-suppliers', {
                    type: 'bar',
                    data: {
                        labels: recordsBySupplier.labels,
                        datasets: [{ data: recordsBySupplier.values, backgroundColor: '#6366f1', borderRadius: 6, maxBarThickness: 28 }],
                    },
                    options: horizontalBarOptions(24),
                });

                chartJs('analytics-overview-afl-projects', {
                    type: 'bar',
                    data: {
                        labels: aflByProject.labels,
                        datasets: [{ data: aflByProject.values, backgroundColor: '#f43f5e', borderRadius: 6, maxBarThickness: 28 }],
                    },
                    options: horizontalBarOptions(24),
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

            nokia() {
                if (typeof Chart === 'undefined') {
                    return;
                }

                chartJs('analytics-nokia-status', {
                    type: 'bar',
                    data: {
                        labels: nokiaByStatus.labels,
                        datasets: [{ data: nokiaByStatus.values, backgroundColor: '#0ea5e9', borderRadius: 6, maxBarThickness: 30 }],
                    },
                    options: horizontalBarOptions(26),
                });
            },

            snapshots() {
                if (typeof ApexCharts === 'undefined' || !snapshotSeries || snapshotSeries.labels.length < 2) {
                    return;
                }

                apexArea('analytics-snapshots', [
                    { name: 'Registros FAMs', data: snapshotSeries.records },
                    { name: 'Registros AFL', data: snapshotSeries.afl },
                    { name: 'Registros Nokia', data: snapshotSeries.nokia },
                ], snapshotSeries.labels, ['#6366f1', '#f43f5e', '#0ea5e9']);
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

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('[data-import-cancel]');

            if (form && !window.confirm('Cancelar esta importação? As linhas já processadas serão descartadas.')) {
                event.preventDefault();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeImportModal);
            }
        });

        document.querySelectorAll('[role="dialog"][data-open]').forEach(openImportModal);

        const formatFileSize = (bytes) => {
            if (!Number.isFinite(bytes) || bytes <= 0) {
                return '0 KB';
            }

            if (bytes < 1024) {
                return `${bytes} B`;
            }

            if (bytes < 1024 * 1024) {
                return `${(bytes / 1024).toFixed(1)} KB`;
            }

            return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
        };

        document.querySelectorAll('[data-import-file]').forEach((input) => {
            const form = input.closest('form');

            if (!form) {
                return;
            }

            const dropzone = form.querySelector('[data-import-dropzone]');
            const preview = form.querySelector('[data-import-file-preview]');
            const fileName = form.querySelector('[data-import-file-name]');
            const fileSize = form.querySelector('[data-import-file-size]');
            const fileTitle = form.querySelector('[data-import-file-title]');
            const fileHint = form.querySelector('[data-import-file-hint]');
            const clear = form.querySelector('[data-import-file-clear]');

            const render = () => {
                const file = input.files?.[0] ?? null;

                if (!file) {
                    preview?.classList.add('hidden');
                    preview?.classList.remove('flex');

                    if (fileTitle) {
                        fileTitle.textContent = 'Selecionar planilha';
                    }

                    if (fileHint) {
                        fileHint.textContent = fileHint.dataset.defaultHint ?? fileHint.textContent;
                    }

                    return;
                }

                if (fileName) {
                    fileName.textContent = file.name;
                }

                if (fileSize) {
                    fileSize.textContent = formatFileSize(file.size);
                }

                preview?.classList.remove('hidden');
                preview?.classList.add('flex');

                if (fileTitle) {
                    fileTitle.textContent = 'Trocar planilha';
                }

                if (fileHint) {
                    fileHint.textContent = 'Clique para escolher outro arquivo.';
                }
            };

            if (fileHint) {
                fileHint.dataset.defaultHint = fileHint.textContent;
            }

            input.addEventListener('change', render);

            clear?.addEventListener('click', () => {
                input.value = '';
                render();
            });

            dropzone?.addEventListener('dragover', (event) => {
                event.preventDefault();
                dropzone.classList.add('ring-2', 'ring-gray-400/50');
            });

            dropzone?.addEventListener('dragleave', () => {
                dropzone.classList.remove('ring-2', 'ring-gray-400/50');
            });

            dropzone?.addEventListener('drop', (event) => {
                event.preventDefault();
                dropzone.classList.remove('ring-2', 'ring-gray-400/50');

                const file = event.dataTransfer?.files?.[0];

                if (!file) {
                    return;
                }

                const transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
                render();
            });

            render();
        });

        const importHistory = document.querySelector('[data-import-history]');
        const importToastRoot = document.querySelector('[data-import-toast-root]');
        const importToastBody = document.querySelector('[data-import-toast-body]');
        const importToastToggle = document.querySelector('[data-import-toast-toggle]');
        const importToastChevron = document.querySelector('[data-import-toast-chevron]');
        const importHeaderIndicator = document.querySelector('[data-import-header-indicator]');
        const importStatusUrl = @json(route('analytics.imports.status'));
        let importPoll = null;
        let importToastHideTimer = null;

        const setImportProcessing = (processing) => {
            if (importHeaderIndicator) {
                importHeaderIndicator.classList.toggle('hidden', !processing);
            }

            document.querySelectorAll('[data-import-file], [data-import-submit]').forEach((field) => {
                field.disabled = processing;
            });

            document.querySelectorAll('[data-import-dropzone]').forEach((dropzone) => {
                dropzone.classList.toggle('pointer-events-none', processing);
                dropzone.classList.toggle('opacity-60', processing);
            });
        };

        const showImportToast = () => {
            if (!importToastRoot) {
                return;
            }

            importToastRoot.classList.remove('hidden');

            if (importToastHideTimer) {
                clearTimeout(importToastHideTimer);
                importToastHideTimer = null;
            }
        };

        const hideImportToast = () => {
            importToastRoot?.classList.add('hidden');
        };

        const toggleImportToast = () => {
            if (!importToastRoot) {
                return;
            }

            const collapsed = importToastRoot.classList.toggle('is-collapsed');

            importToastBody?.classList.toggle('hidden', collapsed);
            importToastChevron?.classList.toggle('rotate-180', collapsed);
            importToastToggle?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        };

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        }[char]));

        const showFinishedImport = (item) => {
            if (!importToastBody || !item) {
                return;
            }

            const tones = {
                completed: ['border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10', 'bg-emerald-600', 'text-emerald-900 dark:text-emerald-200', 'text-emerald-700 dark:text-emerald-300'],
                failed: ['border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10', 'bg-red-600', 'text-red-900 dark:text-red-200', 'text-red-700 dark:text-red-300'],
                canceled: ['border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10', 'bg-amber-600', 'text-amber-900 dark:text-amber-200', 'text-amber-700 dark:text-amber-300'],
            };

            const [card, icon, title, text] = tones[item.status] || tones.completed;
            const iconPath = item.status === 'completed'
                ? 'm4.5 12.75 6 6 9-13.5'
                : item.status === 'canceled'
                    ? 'M6 18 18 6M6 6l12 12'
                    : 'M12 9v3.75m0 3h.008v.008H12v-.008Z';

            importToastBody.innerHTML = `
                <div class="rounded-xl border p-4 ${card}">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-white ${icon}">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="${iconPath}" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold ${title}">${escapeHtml(item.status_label ?? 'Finalizada')}</p>
                            <p class="mt-0.5 truncate text-xs ${text}">${escapeHtml(item.original_name ?? '')}</p>
                            <p class="mt-1 text-xs ${text}">${escapeHtml(item.summary ?? '')}</p>
                        </div>
                    </div>
                </div>
            `;

            showImportToast();
            importToastHideTimer = setTimeout(hideImportToast, 8000);
        };

        const pollImportHistory = async () => {
            try {
                const response = await fetch(importStatusUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const pending = payload.pending === true;

                if (typeof payload.html === 'string' && importHistory) {
                    importHistory.innerHTML = payload.html;
                }

                if (pending) {
                    if (importToastBody && typeof payload.toast_html === 'string') {
                        importToastBody.innerHTML = payload.toast_html;
                    }

                    showImportToast();
                } else if (importPoll) {
                    showFinishedImport(Array.isArray(payload.imports) ? payload.imports[0] : null);

                    clearInterval(importPoll);
                    importPoll = null;
                }

                setImportProcessing(pending);
            } catch (error) {
                console.error('Analytics: falha ao atualizar as importações', error);
            }
        };

        importToastToggle?.addEventListener('click', toggleImportToast);

        setImportProcessing(@json($hasPendingImports));

        if (@json($hasPendingImports)) {
            showImportToast();
            pollImportHistory();
            importPoll = setInterval(pollImportHistory, 2000);
        }
    })();
</script>
