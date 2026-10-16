<x-layouts.admin title="Backups">
    @php
        $secondaryButton = 'inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40';
        $primaryButton = 'inline-flex items-center justify-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60';
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Administração</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="rectangle-stack" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Backups
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Gere, baixe e restaure cópias completas do banco de dados e dos arquivos enviados.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                class="{{ $secondaryButton }}"
                data-modal-open="backup-import-modal"
            >
                <x-icon name="arrow-up-tray" class="h-4 w-4" />
                Importar backup
            </button>

            <form method="POST" action="{{ route('admin.backups.store') }}">
                @csrf
                <button type="submit" class="{{ $primaryButton }}">
                    <x-icon name="plus" class="h-4 w-4" />
                    Novo backup
                </button>
            </form>
        </div>
    </div>

    @error('backup')
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            {{ $message }}
        </div>
    @enderror

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="rectangle-stack" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Total de backups</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="check-circle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['completed'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Concluídos</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="arrow-path" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['in_progress'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Em andamento</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:shadow-lg hover:shadow-red-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-red-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                <x-icon name="exclamation-circle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['failed'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Falharam</p>
        </div>
    </div>

    @if ($stats['in_progress'] > 0 || $stats['restoring'] > 0)
        <div
            id="backup-progress"
            class="mt-6 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300"
        >
            <x-icon name="arrow-path" class="h-5 w-5 animate-spin" />
            <div>
                @if ($stats['restoring'] > 0)
                    <p class="font-semibold">Restauração em andamento</p>
                    <p class="text-amber-700 dark:text-amber-400">A aplicação está em manutenção. Esta página será atualizada automaticamente quando terminar.</p>
                @else
                    <p class="font-semibold">Backup em andamento</p>
                    <p class="text-amber-700 dark:text-amber-400">Esta página será atualizada automaticamente quando o backup terminar.</p>
                @endif
            </div>
        </div>

        <script>
            (function poll() {
                fetch(@json(route('admin.backups.status')), { headers: { 'Accept': 'application/json' } })
                    .then((response) => response.json())
                    .then((backups) => {
                        const busyStates = ['pending', 'running'];
                        const busy = backups.some((backup) =>
                            busyStates.includes(backup.status) || busyStates.includes(backup.restore_status)
                        );

                        if (busy) {
                            setTimeout(poll, 4000);
                        } else {
                            window.location.reload();
                        }
                    })
                    .catch(() => setTimeout(poll, 8000));
            })();
        </script>
    @endif

    <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50/80 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3.5 font-medium">Solicitado em</th>
                        <th class="px-5 py-3.5 font-medium">Status</th>
                        <th class="px-5 py-3.5 font-medium">Tamanho</th>
                        <th class="px-5 py-3.5 font-medium">Por</th>
                        <th class="px-5 py-3.5 text-right font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($backups as $backup)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                            <td class="whitespace-nowrap px-5 py-4">
                                <x-relative-time :value="$backup->created_at" class="font-medium text-gray-900 dark:text-gray-100" />
                                @if ($backup->completed_at)
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        Concluído <x-relative-time :value="$backup->completed_at" />
                                    </p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-backup-status :backup="$backup" />
                                    <x-backup-restore-status :backup="$backup" />
                                </div>
                                @if ($backup->error)
                                    <p class="mt-1 max-w-md text-xs text-red-600 dark:text-red-400">{{ $backup->error }}</p>
                                @endif
                                @if ($backup->restore_error)
                                    <p class="mt-1 max-w-md text-xs text-red-600 dark:text-red-400">{{ $backup->restore_error }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">
                                {{ $backup->size !== null ? \Illuminate\Support\Number::fileSize($backup->size) : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">
                                {{ $backup->user?->full_name ?? 'Sistema' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($backup->isDownloadable())
                                        <a
                                            href="{{ route('admin.backups.download', $backup) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-indigo-500/40"
                                        >
                                            <x-icon name="arrow-down-tray" class="h-4 w-4" />
                                            Baixar
                                        </a>
                                    @endif

                                    @if ($backup->isRestorable())
                                        <form
                                            method="POST"
                                            action="{{ route('admin.backups.restore', $backup) }}"
                                            data-confirm="Restaurar este backup sobre a aplicação? Os dados atuais do banco e os arquivos enviados serão substituídos. Um backup de segurança do estado atual é criado antes."
                                            data-confirm-title="Restaurar backup"
                                            data-confirm-label="Restaurar"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-medium text-amber-700 transition hover:border-amber-300 hover:text-amber-800 dark:border-amber-500/40 dark:text-amber-300 dark:hover:border-amber-400"
                                                title="Restaurar este backup"
                                            >
                                                <x-icon name="arrow-uturn-left" class="h-4 w-4" />
                                                Restaurar
                                            </button>
                                        </form>
                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route('admin.backups.destroy', $backup) }}"
                                        data-confirm="Excluir este backup definitivamente?"
                                        data-confirm-title="Excluir backup"
                                        data-confirm-label="Excluir"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 transition hover:bg-red-50 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-500/15 dark:hover:text-red-400"
                                            title="Excluir"
                                        >
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-16 text-center" colspan="5">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                    <x-icon name="rectangle-stack" class="h-6 w-6" />
                                </span>
                                <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">Nenhum backup gerado</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crie o primeiro backup para proteger os dados da plataforma.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Exibindo
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $backups->count() }}</span>
                de
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $backups->total() }}</span>
                backups
            </p>

            <x-pagination :paginator="$backups" />
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-5 text-sm text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        <div class="flex items-start gap-3">
            <x-icon name="document-text" class="mt-0.5 h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500" />
            <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">O que é incluído</p>
                <p class="mt-1">Cada arquivo <code>.zip</code> contém o dump do banco de dados (<code>database/database.sql</code>), todos os arquivos enviados em <code>storage/app</code> e um <code>manifest.json</code> com os metadados do backup.</p>
                <p class="mt-3 font-medium text-gray-900 dark:text-gray-100">Restauração</p>
                <p class="mt-1">Restaurar substitui os dados do banco e os arquivos enviados pelos do backup. A aplicação entra em manutenção durante o processo e um backup de segurança do estado atual é criado automaticamente. Tabelas internas (fila, cache, sessões e o próprio registro de backups) não são afetadas.</p>
                <p class="mt-3 font-medium text-gray-900 dark:text-gray-100">Importação</p>
                <p class="mt-1">Você também pode importar um arquivo <code>.zip</code> de backup gerado previamente (por exemplo, baixado de outra instância). Ele passa a aparecer na lista e pode ser baixado ou restaurado como qualquer outro.</p>
            </div>
        </div>
    </div>

    <div
        id="backup-import-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="backup-import-modal-title"
        @if ($errors->any() && old('modal') === 'import') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="backup-import-modal-title" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <x-icon name="arrow-up-tray" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                        Importar backup
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Envie um arquivo <code>.zip</code> de backup gerado previamente por esta aplicação.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            @if ($errors->any() && old('modal') === 'import')
                <div
                    data-modal-error
                    class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
                >
                    <p class="font-medium">O backup não foi importado. Corrija os itens abaixo e tente novamente:</p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @forelse ($errors->get('file') as $message)
                            <li>{{ $message }}</li>
                        @empty
                            <li>Verifique o arquivo enviado.</li>
                        @endforelse
                    </ul>
                </div>
            @endif

            <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-300">
                <p>O arquivo deve conter o dump do banco de dados em <code>database/database.sql</code>, os arquivos enviados em <code>storage/app</code> e o <code>manifest.json</code>.</p>
                <p class="mt-1">Após a importação, o backup aparece na lista e pode ser baixado ou restaurado normalmente.</p>
            </div>

            <form method="POST" action="{{ route('admin.backups.import') }}" enctype="multipart/form-data" class="mt-5">
                @csrf
                <input type="hidden" name="modal" value="import">

                <label for="import-file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Arquivo de backup (.zip)</label>
                <input
                    type="file"
                    name="file"
                    id="import-file"
                    accept=".zip"
                    required
                    class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white"
                >
                @error('file')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        <x-icon name="arrow-up-tray" class="h-4 w-4" />
                        Importar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const openBackupModal = (modal) => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            const error = modal.querySelector('[data-modal-error]');
            if (error) {
                error.scrollIntoView({ block: 'center' });
            }
        };

        const closeBackupModal = (modal) => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        document.addEventListener('click', (event) => {
            const opener = event.target.closest('[data-modal-open]');
            if (opener) {
                event.preventDefault();

                const modal = document.getElementById(opener.dataset.modalOpen);
                if (modal) {
                    openBackupModal(modal);
                }

                return;
            }

            const closer = event.target.closest('[data-modal-close]');
            if (closer) {
                closeBackupModal(closer.closest('[role="dialog"]'));
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeBackupModal);
            }
        });

        document.querySelectorAll('[role="dialog"][data-open]').forEach(openBackupModal);
    </script>
</x-layouts.admin>
