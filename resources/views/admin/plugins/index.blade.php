<x-layouts.admin title="Plugins">
    @php
        $primaryButton = 'inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500';
        $secondaryButton = 'inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40';
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Administração</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon name="cog-6-tooth" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                Plugins
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Habilite extensões instaladas em <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">plugins/</code> sem alterar o código da aplicação.
            </p>
        </div>

        <span class="{{ $secondaryButton }} cursor-default">
            <x-icon name="folder" class="h-4 w-4" />
            {{ count(app(\App\Plugins\PluginRepository::class)->paths()) }} diretório(s) monitorado(s)
        </span>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="cog-6-tooth" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Plugins instalados</p>
        </div>

        <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="check-circle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['enabled'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Habilitados</p>
        </div>

        <div class="rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="x-circle" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $stats['disabled'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Desabilitados</p>
        </div>
    </div>

    <x-ui.alert tone="info" icon="arrow-path" class="mt-6">
        A mudança vale para as próximas requisições. Workers de fila e servidores de longa duração precisam de
        <code class="rounded bg-white/60 px-1.5 py-0.5 text-xs dark:bg-black/20">php artisan queue:restart</code>.
        Após criar um plugin, rode <code class="rounded bg-white/60 px-1.5 py-0.5 text-xs dark:bg-black/20">composer dump-autoload</code>.
    </x-ui.alert>

    <section class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="arrow-up-tray" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Instalar plugin via ZIP
                </h2>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Envie um arquivo <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">.zip</code> contendo um
                    <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">plugin.json</code> na raiz ou dentro de uma única pasta.
                    O plugin é instalado desabilitado — revise o código antes de habilitá-lo.
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.plugins.store') }}"
            enctype="multipart/form-data"
            class="mt-4 flex flex-wrap items-end gap-3"
        >
            @csrf

            <div class="w-full max-w-md">
                <label for="plugin-file" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Arquivo do plugin</label>
                <input
                    id="plugin-file"
                    name="file"
                    type="file"
                    accept=".zip,application/zip"
                    required
                    class="mt-1 block w-full cursor-pointer rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm transition file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-indigo-600 hover:file:bg-indigo-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:file:bg-indigo-500/10 dark:file:text-indigo-300"
                >
            </div>

            <button type="submit" class="{{ $primaryButton }}">
                <x-icon name="arrow-up-tray" class="h-4 w-4" />
                Instalar plugin
            </button>
        </form>

        @error('file')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror

        <p class="mt-3 flex items-start gap-2 text-xs text-amber-600 dark:text-amber-400">
            <x-icon name="exclamation-triangle" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
            Plugins executam código PHP com as permissões da aplicação. Instale apenas arquivos de origem confiável.
        </p>
    </section>

    @if ($plugins->isEmpty())
        <div class="mt-6 rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <x-ui.empty-state
                icon="cog-6-tooth"
                title="Nenhum plugin encontrado"
                description="Crie um plugin com 'php artisan plugin:make meu-plugin' para começar."
            />
        </div>
    @else
        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50/80 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5 font-medium">Plugin</th>
                            <th class="px-5 py-3.5 font-medium">Versão</th>
                            <th class="px-5 py-3.5 font-medium">Providers</th>
                            <th class="px-5 py-3.5 font-medium">Status</th>
                            <th class="px-5 py-3.5 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($plugins as $plugin)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                            <x-icon :name="$plugin->get('icon', 'cog-6-tooth')" class="h-4 w-4" />
                                        </span>

                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $plugin->getName() }}</p>
                                            <p class="mt-0.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $plugin->getAlias() }}</p>

                                            @if ($plugin->getDescription())
                                                <p class="mt-1 max-w-xl text-xs text-gray-500 dark:text-gray-400">{{ $plugin->getDescription() }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">
                                    <x-ui.badge tone="gray">v{{ $plugin->getVersion() ?? '—' }}</x-ui.badge>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">
                                    {{ count($plugin->providers()) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @if ($plugin->enabled())
                                        <x-ui.badge tone="green">Habilitado</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="amber">Desabilitado</x-ui.badge>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <form method="POST" action="{{ route('admin.plugins.update', ['alias' => $plugin->getAlias()]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="enabled" value="{{ $plugin->enabled() ? 0 : 1 }}">

                                        <button type="submit" class="{{ $primaryButton }}">
                                            <x-icon :name="$plugin->enabled() ? 'x-circle' : 'play'" class="h-4 w-4" />
                                            {{ $plugin->enabled() ? 'Desabilitar' : 'Habilitar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-layouts.admin>
