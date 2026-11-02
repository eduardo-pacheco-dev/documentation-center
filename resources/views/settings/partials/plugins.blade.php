<section id="plugins" class="{{ $cardClass }} scroll-mt-6">
    <div class="{{ $headerClass }}">
        <span class="{{ $tileClass }}">
            <x-icon name="cog-6-tooth" class="h-5 w-5" />
        </span>

        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="{{ $titleClass }}">Plugins</h2>
                <x-ui.badge tone="indigo">{{ $pluginStats['total'] }}</x-ui.badge>
                <x-ui.badge tone="green">{{ $pluginStats['enabled'] }} habilitados</x-ui.badge>

                @if ($pluginStats['disabled'] > 0)
                    <x-ui.badge tone="amber">{{ $pluginStats['disabled'] }} desabilitados</x-ui.badge>
                @endif
            </div>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Extensões instaladas em <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600 dark:bg-white/[0.06] dark:text-gray-300">plugins/</code>.
                A mudança vale para as próximas requisições; reinicie os workers com
                <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600 dark:bg-white/[0.06] dark:text-gray-300">php artisan queue:restart</code>.
            </p>
        </div>
    </div>

    <div class="{{ $bodyClass }}">
        <form
            method="POST"
            action="{{ route('settings.plugins.store') }}"
            enctype="multipart/form-data"
            class="rounded-2xl border border-dashed border-gray-300 p-4 dark:border-white/15"
        >
            @csrf

            <label for="plugin-file" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Instalar plugin via ZIP</label>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Envie um <code class="rounded bg-gray-100 px-1 py-0.5 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300">.zip</code> com um
                <code class="rounded bg-gray-100 px-1 py-0.5 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300">plugin.json</code> na raiz ou dentro de uma única pasta.
                O plugin é instalado desabilitado.
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <input
                    id="plugin-file"
                    name="file"
                    type="file"
                    accept=".zip,application/zip"
                    required
                    class="block h-10 w-full max-w-xs cursor-pointer rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-600 transition file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-1 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:file:bg-white/10 dark:file:text-gray-200 dark:hover:file:bg-white/20"
                >

                <x-ui.action type="submit" icon="arrow-up-tray">
                    Instalar
                </x-ui.action>
            </div>

            @error('file')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            @error('plugin')
                <div class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                    {{ $message }}
                </div>
            @enderror

            <p class="mt-3 flex items-start gap-2 text-xs text-amber-600 dark:text-amber-400">
                <x-icon name="exclamation-triangle" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                Plugins executam código PHP com as permissões da aplicação. Instale apenas arquivos de origem confiável.
            </p>
        </form>

        @if ($plugins->isEmpty())
            <div class="mt-4 rounded-2xl border border-gray-200/80 dark:border-white/10">
                <x-ui.empty-state
                    icon="cog-6-tooth"
                    title="Nenhum plugin instalado"
                    description="Envie um arquivo ZIP acima ou crie um com 'php artisan plugin:make meu-plugin'."
                    tone="indigo"
                    shape="square"
                />
            </div>
        @else
            <ul class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/80 dark:divide-white/10 dark:border-white/10">
                @foreach ($plugins as $plugin)
                    <li class="flex flex-col gap-3 px-4 py-4 transition hover:bg-gray-50/70 sm:flex-row sm:items-center dark:hover:bg-white/[0.03]">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <x-icon :name="$plugin->get('icon', 'cog-6-tooth')" class="h-4 w-4" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $plugin->getName() }}</p>
                                <x-ui.badge tone="gray">v{{ $plugin->getVersion() ?? '—' }}</x-ui.badge>

                                @if ($plugin->enabled())
                                    <x-ui.badge tone="green">Habilitado</x-ui.badge>
                                @else
                                    <x-ui.badge tone="amber">Desabilitado</x-ui.badge>
                                @endif
                            </div>

                            <p class="mt-0.5 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $plugin->getAlias() }}</p>

                            @if ($plugin->getDescription())
                                <p class="mt-1 max-w-xl text-xs text-gray-500 dark:text-gray-400">{{ $plugin->getDescription() }}</p>
                            @endif
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2 sm:justify-end">
                            <form method="POST" action="{{ route('settings.plugins.update', ['alias' => $plugin->getAlias()]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="enabled" value="{{ $plugin->enabled() ? 0 : 1 }}">

                                <x-ui.action
                                    type="submit"
                                    size="sm"
                                    :variant="$plugin->enabled() ? 'secondary' : 'primary'"
                                    :icon="$plugin->enabled() ? 'x-circle' : 'play'"
                                >
                                    {{ $plugin->enabled() ? 'Desabilitar' : 'Habilitar' }}
                                </x-ui.action>
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

                                <x-ui.action type="submit" size="sm" variant="danger" icon="trash" aria-label="Remover {{ $plugin->getName() }}">
                                    Remover
                                </x-ui.action>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
