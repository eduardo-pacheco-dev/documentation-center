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
                Gere e baixe cópias completas do banco de dados e dos arquivos enviados.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.backups.store') }}">
            @csrf
            <button type="submit" class="{{ $primaryButton }}">
                <x-icon name="plus" class="h-4 w-4" />
                Novo backup
            </button>
        </form>
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

    @if ($stats['in_progress'] > 0)
        <div
            id="backup-progress"
            class="mt-6 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300"
        >
            <x-icon name="arrow-path" class="h-5 w-5 animate-spin" />
            <div>
                <p class="font-semibold">Backup em andamento</p>
                <p class="text-amber-700 dark:text-amber-400">Esta página será atualizada automaticamente quando o backup terminar.</p>
            </div>
        </div>

        <script>
            (function poll() {
                fetch(@json(route('admin.backups.status')), { headers: { 'Accept': 'application/json' } })
                    .then((response) => response.json())
                    .then((backups) => {
                        const busy = backups.some((backup) => ['pending', 'running'].includes(backup.status));

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
                                <x-backup-status :backup="$backup" />
                                @if ($backup->error)
                                    <p class="mt-1 max-w-md text-xs text-red-600 dark:text-red-400">{{ $backup->error }}</p>
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
            </div>
        </div>
    </div>
</x-layouts.admin>
