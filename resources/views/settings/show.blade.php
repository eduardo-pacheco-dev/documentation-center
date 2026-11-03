<x-layouts.admin title="Configurações">
    @php
        $sections = ['aparencia', 'modulos'];

        if ($user->is_admin) {
            $sections[] = 'plugins';
        }

        $sections = [...$sections, 'senha', 'conta'];

        $cardClass = 'rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]';
        $headerClass = 'flex items-start gap-3 p-5 sm:p-6';
        $bodyClass = 'border-t border-gray-100 p-5 sm:p-6 dark:border-white/10';
        $tileClass = 'inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400';
        $titleClass = 'text-base font-semibold tracking-tight text-gray-900 dark:text-white';
        $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
        $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:placeholder:text-gray-500 dark:focus:bg-white/5';
        $navIdle = 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.04]';
        $navActive = 'bg-indigo-600 text-white';

        $moduleMeta = collect($orderModules)->mapWithKeys(fn ($module): array => [
            $module->value => [
                'label' => $module->label(),
                'group' => \App\Enums\AdminModule::sectionFor($user, $module),
            ],
        ])->all();
    @endphp

    <x-ui.page-header
        category="Conta"
        title="Configurações"
        description="Gerencie a aparência, os módulos, os plugins, a segurança e a sua conta."
        icon="cog-6-tooth"
    />

    <div class="mt-7 grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-start">
        <aside class="lg:sticky lg:top-6 lg:self-start">
            <div class="rounded-3xl border border-gray-200/80 bg-white p-2.5 dark:border-white/10 dark:bg-[#171716]">
                <p class="px-3 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Seções</p>

                <nav
                    x-data="{
                        active: 'aparencia',
                        sections: @js($sections),
                        init() {
                            const hash = window.location.hash.slice(1);

                            if (this.sections.includes(hash)) {
                                this.active = hash;
                            }

                            const state = this;

                            if (typeof IntersectionObserver !== 'function') {
                                return;
                            }

                            const observer = new IntersectionObserver((entries) => {
                                entries.forEach((entry) => {
                                    if (entry.isIntersecting) {
                                        state.active = entry.target.id;
                                    }
                                });
                            }, { rootMargin: '-10% 0px -55% 0px' });

                            this.sections.forEach((id) => {
                                const element = document.getElementById(id);

                                if (element) {
                                    observer.observe(element);
                                }
                            });
                        },
                    }"
                    class="space-y-0.5"
                    aria-label="Seções das configurações"
                >
                    <a
                        href="#aparencia"
                        @click="active = 'aparencia'"
                        x-bind:class="active === 'aparencia' ? '{{ $navActive }}' : '{{ $navIdle }}'"
                        class="group flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                    >
                        <x-icon name="sun" class="h-4 w-4 shrink-0" x-bind:class="active === 'aparencia' ? 'text-white' : 'text-amber-500'" />
                        <span class="flex-1 truncate">Aparência</span>
                    </a>

                    <a
                        href="#modulos"
                        @click="active = 'modulos'"
                        x-bind:class="active === 'modulos' ? '{{ $navActive }}' : '{{ $navIdle }}'"
                        class="group flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                    >
                        <x-icon name="squares-2x2" class="h-4 w-4 shrink-0" x-bind:class="active === 'modulos' ? 'text-white' : 'text-indigo-500'" />
                        <span class="flex-1 truncate">Módulos</span>
                        <x-ui.badge tone="gray">{{ count($enabledModules) }}</x-ui.badge>
                    </a>

                    @if ($user->is_admin)
                        <a
                            href="#plugins"
                            @click="active = 'plugins'"
                            x-bind:class="active === 'plugins' ? '{{ $navActive }}' : '{{ $navIdle }}'"
                            class="group flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                        >
                            <x-icon name="cog-6-tooth" class="h-4 w-4 shrink-0" x-bind:class="active === 'plugins' ? 'text-white' : 'text-gray-400'" />
                            <span class="flex-1 truncate">Plugins</span>

                            @if ($pluginStats['total'] > 0)
                                <x-ui.badge tone="gray">{{ $pluginStats['total'] }}</x-ui.badge>
                            @endif
                        </a>
                    @endif

                    <a
                        href="#senha"
                        @click="active = 'senha'"
                        x-bind:class="active === 'senha' ? '{{ $navActive }}' : '{{ $navIdle }}'"
                        class="group flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                    >
                        <x-icon name="eye-slash" class="h-4 w-4 shrink-0" x-bind:class="active === 'senha' ? 'text-white' : 'text-gray-400'" />
                        <span class="flex-1 truncate">Alterar senha</span>
                    </a>

                    <a
                        href="#conta"
                        @click="active = 'conta'"
                        x-bind:class="active === 'conta' ? 'bg-red-600 text-white' : 'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10'"
                        class="group flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                    >
                        <x-icon name="trash" class="h-4 w-4 shrink-0" x-bind:class="active === 'conta' ? 'text-white' : 'text-red-500'" />
                        <span class="flex-1 truncate">Excluir conta</span>
                    </a>
                </nav>
            </div>
        </aside>

        <div class="min-w-0 space-y-4">
            <form
                id="aparencia"
                method="POST"
                action="{{ route('settings.theme.update') }}"
                class="{{ $cardClass }} scroll-mt-6"
            >
                @csrf
                @method('PUT')

                <div class="{{ $headerClass }}">
                    <span class="{{ $tileClass }}">
                        <x-icon name="sun" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 class="{{ $titleClass }}">Aparência</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Escolha como o painel será exibido para você.</p>
                    </div>
                </div>

                <div class="{{ $bodyClass }}">
                    <label for="theme-select" class="{{ $labelClass }}">Tema</label>

                    <div
                        class="relative mt-1.5 w-full max-w-xs"
                        x-data="{
                            open: false,
                            value: @json(old('theme', $user->theme)),
                            options: {
                                system: { label: 'Sistema', description: 'Segue as preferências do seu dispositivo' },
                                light: { label: 'Claro', description: 'Sempre com fundo claro' },
                                dark: { label: 'Escuro', description: 'Sempre com fundo escuro' },
                            },
                            get selected() {
                                return this.options[this.value] ?? this.options.system;
                            },
                            init() {
                                if (!this.options[this.value]) {
                                    this.value = 'system';
                                }
                            },
                            select(key) {
                                this.value = key;
                                this.open = false;
                            },
                        }"
                        @keydown.escape.window="open = false"
                    >
                        <input type="hidden" name="theme" :value="value">

                        <button
                            id="theme-select"
                            type="button"
                            @click="open = !open"
                            aria-haspopup="listbox"
                            :aria-expanded="open"
                            class="flex h-10 w-full items-center justify-between gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 text-left text-sm text-gray-700 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:focus:bg-white/5"
                        >
                            <span x-text="selected.label"></span>
                            <x-icon name="chevron-up-down" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        </button>

                        <ul
                            x-show="open"
                            x-transition
                            @click.outside="open = false"
                            role="listbox"
                            aria-label="Tema"
                            style="display: none;"
                            class="absolute z-10 mt-1 w-full overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-lg dark:border-white/10 dark:bg-[#1f1f1e]"
                        >
                            <template x-for="([key, option]) in Object.entries(options)" :key="key">
                                <li role="option" :aria-selected="value === key">
                                    <button
                                        type="button"
                                        @click="select(key)"
                                        :class="value === key
                                            ? 'bg-gray-100 text-gray-900 dark:bg-white/10 dark:text-white'
                                            : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5'"
                                        class="flex w-full items-start justify-between gap-3 px-3 py-2 text-left text-sm"
                                    >
                                        <span>
                                            <span class="block font-medium" x-text="option.label"></span>
                                            <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400" x-text="option.description"></span>
                                        </span>

                                        <span x-show="value === key" class="shrink-0">
                                            <x-icon name="check" class="mt-0.5 text-indigo-600 dark:text-indigo-400" />
                                        </span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>

                    @error('theme')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <div class="mt-5">
                        <x-ui.action type="submit" icon="check">
                            Salvar aparência
                        </x-ui.action>
                    </div>
                </div>
            </form>

            <form
                id="modulos"
                method="POST"
                action="{{ route('settings.modules.update') }}"
                class="{{ $cardClass }} scroll-mt-6"
                x-data="{
                    order: @js(array_keys($moduleMeta)),
                    meta: @js($moduleMeta),
                    groupIndexes(group) {
                        return this.order.reduce((indexes, key, index) => {
                            if (this.meta[key] && this.meta[key].group === group) {
                                indexes.push(index);
                            }

                            return indexes;
                        }, []);
                    },
                    move(key, direction) {
                        const group = this.meta[key] ? this.meta[key].group : null;
                        const indexes = this.groupIndexes(group);
                        const position = indexes.indexOf(this.order.indexOf(key));
                        const target = position + direction;

                        if (position < 0 || target < 0 || target >= indexes.length) {
                            return;
                        }

                        const first = indexes[position];
                        const second = indexes[target];

                        [this.order[first], this.order[second]] = [this.order[second], this.order[first]];
                    },
                    isFirst(key) {
                        const indexes = this.groupIndexes(this.meta[key] ? this.meta[key].group : null);

                        return indexes.indexOf(this.order.indexOf(key)) === 0;
                    },
                    isLast(key) {
                        const indexes = this.groupIndexes(this.meta[key] ? this.meta[key].group : null);

                        return indexes.indexOf(this.order.indexOf(key)) === indexes.length - 1;
                    },
                    moveToOtherGroup(key) {
                        if (! this.meta[key]) {
                            return;
                        }

                        this.meta[key].group = this.meta[key].group === 'sidebar' ? 'modules' : 'sidebar';
                    },
                }"
            >
                @csrf
                @method('PUT')

                <div class="{{ $headerClass }}">
                    <span class="{{ $tileClass }}">
                        <x-icon name="squares-2x2" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="{{ $titleClass }}">Módulos</h2>
                            <x-ui.badge tone="indigo">{{ count($enabledModules) }}</x-ui.badge>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Escolha quais módulos aparecem na navegação e ajuste a ordem dos botões na sidebar e no menu Módulos.
                        </p>
                    </div>
                </div>

                <div class="{{ $bodyClass }}">
                    @error('modules')
                        <p class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    @error('modules.*')
                        <p class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    @error('order')
                        <p class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    @error('order.*')
                        <p class="mb-3 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <div class="mb-6">
                        <div class="flex flex-wrap items-end justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Ordem dos botões</h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Use as setas para ordenar e o botão de mover para trocar o botão entre a sidebar e o menu Módulos.</p>
                            </div>
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach (['sidebar' => 'Sidebar', 'modules' => 'Menu Módulos'] as $groupKey => $groupLabel)
                                <div class="rounded-xl border border-gray-200/80 p-3 dark:border-white/10">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $groupLabel }}</p>

                                    <div class="mt-2 space-y-1">
                                        <template x-for="key in order.filter((item) => meta[item].group === '{{ $groupKey }}')" :key="key">
                                            <div class="flex items-center gap-2 rounded-lg border border-gray-200/80 px-3 py-2 dark:border-white/10">
                                                <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200" x-text="meta[key].label"></span>

                                                <button
                                                    type="button"
                                                    @click="moveToOtherGroup(key)"
                                                    class="inline-flex h-7 items-center whitespace-nowrap rounded-md border border-gray-200 px-2 text-[11px] font-medium text-gray-500 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-white/10 dark:text-gray-400 dark:hover:border-indigo-500/40 dark:hover:text-indigo-400"
                                                    x-text="meta[key].group === 'sidebar' ? 'Mover para Módulos' : 'Mover para Sidebar'"
                                                ></button>

                                                <button
                                                    type="button"
                                                    @click="move(key, -1)"
                                                    :disabled="isFirst(key)"
                                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 disabled:cursor-not-allowed disabled:opacity-30 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                                                    aria-label="Mover para cima"
                                                >
                                                    <x-icon name="chevron-up" class="h-4 w-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    @click="move(key, 1)"
                                                    :disabled="isLast(key)"
                                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 disabled:cursor-not-allowed disabled:opacity-30 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                                                    aria-label="Mover para baixo"
                                                >
                                                    <x-icon name="chevron-down" class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </template>

                                        <p
                                            x-show="order.filter((item) => meta[item].group === '{{ $groupKey }}').length === 0"
                                            class="rounded-lg border border-dashed border-gray-200 px-3 py-2 text-xs text-gray-400 dark:border-white/10 dark:text-gray-500"
                                        >
                                            Nenhum botão neste grupo.
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <template x-for="key in order" :key="'order-' + key">
                            <input type="hidden" name="order[]" :value="key">
                        </template>

                        <template x-for="key in order" :key="'section-' + key">
                            <input type="hidden" :name="'sections[' + key + ']'" :value="meta[key].group">
                        </template>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($modules as $module)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200/80 p-3 transition hover:border-indigo-200 dark:border-white/10 dark:hover:border-indigo-500/40">
                                <input
                                    type="checkbox"
                                    name="modules[]"
                                    value="{{ $module->value }}"
                                    @checked(in_array($module->value, $enabledModules, true))
                                    class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500/30 dark:border-white/20 dark:bg-white/[0.03] dark:text-indigo-400"
                                >

                                <span class="min-w-0">
                                    <span class="flex items-center gap-2 text-sm font-medium text-gray-800 dark:text-gray-100">
                                        <x-icon :name="$module->icon()" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        {{ $module->label() }}
                                    </span>
                                    <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Exibir na navegação</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-5">
                        <x-ui.action type="submit" icon="check">
                            Salvar módulos
                        </x-ui.action>
                    </div>
                </div>
            </form>

            @if ($user->is_admin)
                @include('settings.partials.plugins', [
                    'cardClass' => $cardClass,
                    'headerClass' => $headerClass,
                    'bodyClass' => $bodyClass,
                    'tileClass' => $tileClass,
                    'titleClass' => $titleClass,
                    'inputClass' => $inputClass,
                ])
            @endif

            <form
                id="senha"
                method="POST"
                action="{{ route('settings.password.update') }}"
                class="{{ $cardClass }} scroll-mt-6"
            >
                @csrf
                @method('PUT')

                <div class="{{ $headerClass }}">
                    <span class="{{ $tileClass }}">
                        <x-icon name="eye-slash" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 class="{{ $titleClass }}">Alterar senha</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use uma senha forte que você não utiliza em outros serviços.</p>
                    </div>
                </div>

                <div class="{{ $bodyClass }} space-y-4">
                    <div>
                        <label for="current_password" class="{{ $labelClass }}">Senha atual</label>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="{{ $inputClass }}"
                        >
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="{{ $labelClass }}">Nova senha</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="{{ $inputClass }}"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="{{ $labelClass }}">Confirmar nova senha</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="{{ $inputClass }}"
                        >
                    </div>

                    <div class="pt-1">
                        <x-ui.action type="submit" icon="check">
                            Atualizar senha
                        </x-ui.action>
                    </div>
                </div>
            </form>

            <form
                id="conta"
                method="POST"
                action="{{ route('settings.account.destroy') }}"
                class="scroll-mt-6 rounded-3xl border border-red-200 bg-white dark:border-red-900/60 dark:bg-[#171716]"
                data-confirm="Excluir sua conta permanentemente? Esta ação não pode ser desfeita."
                data-confirm-title="Excluir conta"
                data-confirm-label="Excluir conta"
            >
                @csrf
                @method('DELETE')

                <div class="{{ $headerClass }}">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        <x-icon name="trash" class="h-5 w-5" />
                    </span>

                    <div class="min-w-0">
                        <h2 class="{{ $titleClass }} text-red-700 dark:text-red-400">Excluir conta</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Sua conta será removida permanentemente, junto com seus links e arquivos. Essa ação não pode ser desfeita.
                        </p>
                    </div>
                </div>

                <div class="{{ $bodyClass }}">
                    <div class="max-w-sm">
                        <label for="delete_password" class="{{ $labelClass }}">Confirme com sua senha</label>
                        <input
                            id="delete_password"
                            name="delete_password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="{{ $inputClass }} focus:border-red-500 focus:ring-red-500/15"
                        >
                        @error('delete_password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-5">
                        <x-ui.action type="submit" variant="danger" icon="trash">
                            Excluir conta
                        </x-ui.action>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
