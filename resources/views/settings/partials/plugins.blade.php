@php
    $primaryButton = 'inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500';
@endphp

<section
    id="plugins"
    class="scroll-mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="flex items-center gap-2 text-sm font-semibold">
                <x-icon name="cog-6-tooth" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                Plugins
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Extensões instaladas em <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">plugins/</code>.
                A mudança vale para as próximas requisições; reinicie os workers com
                <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">php artisan queue:restart</code>.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-1.5">
            <x-ui.badge tone="gray">{{ $pluginStats['total'] }} instalados</x-ui.badge>
            <x-ui.badge tone="green">{{ $pluginStats['enabled'] }} habilitados</x-ui.badge>
            <x-ui.badge tone="amber">{{ $pluginStats['disabled'] }} desabilitados</x-ui.badge>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('settings.plugins.store') }}"
        enctype="multipart/form-data"
        class="mt-4 rounded-xl border border-dashed border-gray-300 p-4 dark:border-gray-700"
    >
        @csrf

        <label for="plugin-file" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Instalar plugin via ZIP</label>
        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
            Envie um <code class="rounded bg-gray-100 px-1 py-0.5 dark:bg-gray-800">.zip</code> com um
            <code class="rounded bg-gray-100 px-1 py-0.5 dark:bg-gray-800">plugin.json</code> na raiz ou dentro de uma única pasta.
            O plugin é instalado desabilitado.
        </p>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <input
                id="plugin-file"
                name="file"
                type="file"
                accept=".zip,application/zip"
                required
                class="block w-full max-w-xs cursor-pointer rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm transition file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-indigo-600 hover:file:bg-indigo-100 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:file:bg-indigo-500/10 dark:file:text-indigo-300"
            >

            <button type="submit" class="{{ $primaryButton }}">
                <x-icon name="arrow-up-tray" class="h-4 w-4" />
                Instalar
            </button>
        </div>

        @error('file')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror

        <p class="mt-3 flex items-start gap-2 text-xs text-amber-600 dark:text-amber-400">
            <x-icon name="exclamation-triangle" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
            Plugins executam código PHP com as permissões da aplicação. Instale apenas arquivos de origem confiável.
        </p>
    </form>

    @if ($plugins->isEmpty())
        <p class="mt-4 rounded-xl border border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
            Nenhum plugin encontrado. Crie um com
            <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">php artisan plugin:make meu-plugin</code>.
        </p>
    @else
        <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50/80 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Plugin</th>
                            <th class="px-4 py-3 font-medium">Versão</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($plugins as $plugin)
                            <tr class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                <td class="px-4 py-3">
                                    <p class="flex items-center gap-2 font-medium text-gray-900 dark:text-gray-100">
                                        <x-icon :name="$plugin->get('icon', 'cog-6-tooth')" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ $plugin->getName() }}
                                    </p>
                                    <p class="mt-0.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $plugin->getAlias() }}</p>

                                    @if ($plugin->getDescription())
                                        <p class="mt-1 max-w-md text-xs text-gray-500 dark:text-gray-400">{{ $plugin->getDescription() }}</p>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-gray-500 dark:text-gray-400">
                                    v{{ $plugin->getVersion() ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($plugin->enabled())
                                        <x-ui.badge tone="green">Habilitado</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="amber">Desabilitado</x-ui.badge>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('settings.plugins.update', ['alias' => $plugin->getAlias()]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="enabled" value="{{ $plugin->enabled() ? 0 : 1 }}">

                                            <button type="submit" class="{{ $primaryButton }}">
                                                <x-icon :name="$plugin->enabled() ? 'x-circle' : 'play'" class="h-4 w-4" />
                                                {{ $plugin->enabled() ? 'Desabilitar' : 'Habilitar' }}
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route('settings.plugins.destroy', ['alias' => $plugin->getAlias()]) }}"
                                            data-confirm="Remover o plugin {{ $plugin->getName() }}? Os arquivos em plugins/ serão apagados e essa ação não pode ser desfeita."
                                            data-confirm-title="Remover plugin"
                                            data-confirm-label="Remover"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm transition hover:border-red-300 hover:bg-red-50 dark:border-red-900 dark:bg-gray-900 dark:text-red-400 dark:hover:bg-red-950/40"
                                            >
                                                <x-icon name="trash" class="h-4 w-4" />
                                                Remover
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>
