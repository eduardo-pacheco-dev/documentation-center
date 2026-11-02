<x-layouts.admin title="Configurações">
    @php
        $sections = ['aparencia', 'modulos'];

        if ($user->is_admin) {
            $sections[] = 'plugins';
        }

        $sections = [...$sections, 'senha', 'conta'];

        $cardClass = 'scroll-mt-6 border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900';
        $titleClass = 'text-xs font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-gray-400';
        $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
        $inputClass = 'mt-1 block w-full rounded-none border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-gray-400 dark:focus:ring-white/20';
        $buttonClass = 'rounded-none bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200';
        $navBase = 'whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium transition lg:border-b-0 lg:border-l-2';
        $navIdle = 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:bg-gray-800/60 dark:hover:text-gray-100';
    @endphp

    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Conta</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Configurações</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Gerencie a aparência, os módulos, os plugins, a segurança e a sua conta.
        </p>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[13rem_minmax(0,1fr)]">
        <aside class="lg:sticky lg:top-6 lg:self-start">
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
                class="flex gap-1 overflow-x-auto border-b border-gray-200 lg:flex-col lg:gap-0 lg:overflow-visible lg:border-b-0 lg:border-l lg:border-gray-200 dark:border-gray-800"
                aria-label="Seções das configurações"
            >
                <a
                    href="#aparencia"
                    @click="active = 'aparencia'"
                    :class="active === 'aparencia'
                        ? 'border-indigo-600 bg-gray-50 text-gray-900 dark:border-indigo-400 dark:bg-gray-800/60 dark:text-white'
                        : '{{ $navIdle }}'"
                    class="{{ $navBase }}"
                >
                    Aparência
                </a>

                <a
                    href="#modulos"
                    @click="active = 'modulos'"
                    :class="active === 'modulos'
                        ? 'border-indigo-600 bg-gray-50 text-gray-900 dark:border-indigo-400 dark:bg-gray-800/60 dark:text-white'
                        : '{{ $navIdle }}'"
                    class="{{ $navBase }}"
                >
                    Módulos
                </a>

                @if ($user->is_admin)
                    <a
                        href="#plugins"
                        @click="active = 'plugins'"
                        :class="active === 'plugins'
                            ? 'border-indigo-600 bg-gray-50 text-gray-900 dark:border-indigo-400 dark:bg-gray-800/60 dark:text-white'
                            : '{{ $navIdle }}'"
                        class="{{ $navBase }}"
                    >
                        Plugins
                    </a>
                @endif

                <a
                    href="#senha"
                    @click="active = 'senha'"
                    :class="active === 'senha'
                        ? 'border-indigo-600 bg-gray-50 text-gray-900 dark:border-indigo-400 dark:bg-gray-800/60 dark:text-white'
                        : '{{ $navIdle }}'"
                    class="{{ $navBase }}"
                >
                    Alterar senha
                </a>

                <a
                    href="#conta"
                    @click="active = 'conta'"
                    :class="active === 'conta'
                        ? 'border-red-600 bg-red-50 text-red-700 dark:border-red-400 dark:bg-red-950/40 dark:text-red-400'
                        : 'border-transparent text-red-600 hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:border-red-900 dark:hover:bg-red-950/30'"
                    class="{{ $navBase }}"
                >
                    Excluir conta
                </a>
            </nav>
        </aside>

        <div class="min-w-0 max-w-3xl space-y-6">
            <form
                id="aparencia"
                method="POST"
                action="{{ route('settings.theme.update') }}"
                class="{{ $cardClass }}"
            >
                @csrf
                @method('PUT')

                <h2 class="{{ $titleClass }}">Aparência</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Escolha como o painel será exibido para você.</p>

                <div class="mt-4">
                    <label for="theme-select" class="{{ $labelClass }}">Tema</label>

                    <div
                        class="relative mt-1 w-full max-w-xs"
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
                            class="flex w-full items-center justify-between gap-2 rounded-none border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-none focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-gray-400 dark:focus:ring-white/20"
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
                            class="absolute z-10 mt-1 w-full rounded-none border border-gray-200 bg-white py-1 shadow-none dark:border-gray-700 dark:bg-gray-900"
                        >
                            <template x-for="([key, option]) in Object.entries(options)" :key="key">
                                <li role="option" :aria-selected="value === key">
                                    <button
                                        type="button"
                                        @click="select(key)"
                                        :class="value === key
                                            ? 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white'
                                            : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800'"
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
                </div>

                @error('theme')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <button type="submit" class="mt-4 {{ $buttonClass }}">
                    Salvar aparência
                </button>
            </form>

            <form
                id="modulos"
                method="POST"
                action="{{ route('settings.modules.update') }}"
                class="{{ $cardClass }}"
            >
                @csrf
                @method('PUT')

                <h2 class="{{ $titleClass }}">Módulos</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Escolha quais módulos aparecem no menu lateral. O Início permanece sempre ativo.
                </p>

                @error('modules')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                @error('modules.*')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($modules as $module)
                        <label class="flex cursor-pointer items-start gap-3 border border-gray-200 p-3 transition hover:border-gray-400 dark:border-gray-800 dark:hover:border-gray-600">
                            <input
                                type="checkbox"
                                name="modules[]"
                                value="{{ $module->value }}"
                                @checked(in_array($module->value, $enabledModules, true))
                                class="mt-0.5 h-4 w-4 shrink-0 rounded-none border-gray-300 accent-gray-900 focus:ring-gray-900/20 dark:border-gray-600 dark:bg-gray-800"
                            >

                            <span class="min-w-0">
                                <span class="flex items-center gap-2 text-sm font-medium text-gray-800 dark:text-gray-100">
                                    <x-icon :name="$module->icon()" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                    {{ $module->label() }}
                                </span>
                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Exibir no menu lateral</span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="mt-4 {{ $buttonClass }}">
                    Salvar módulos
                </button>
            </form>

            @if ($user->is_admin)
                @include('settings.partials.plugins', ['cardClass' => $cardClass, 'titleClass' => $titleClass])
            @endif

            <form
                id="senha"
                method="POST"
                action="{{ route('settings.password.update') }}"
                class="{{ $cardClass }} space-y-4"
            >
                @csrf
                @method('PUT')

                <div>
                    <h2 class="{{ $titleClass }}">Alterar senha</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use uma senha forte que você não utiliza em outros serviços.</p>
                </div>

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

                <button type="submit" class="{{ $buttonClass }}">
                    Atualizar senha
                </button>
            </form>

            <form
                id="conta"
                method="POST"
                action="{{ route('settings.account.destroy') }}"
                class="scroll-mt-6 space-y-4 border border-red-300 bg-white p-5 dark:border-red-900 dark:bg-gray-900"
                data-confirm="Excluir sua conta permanentemente? Esta ação não pode ser desfeita."
                data-confirm-title="Excluir conta"
                data-confirm-label="Excluir conta"
            >
                @csrf
                @method('DELETE')

                <div>
                    <h2 class="{{ $titleClass }} text-red-700 dark:text-red-400">Excluir conta</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Sua conta será removida permanentemente, junto com seus links e arquivos. Essa ação não pode ser desfeita.
                    </p>
                </div>

                <div class="max-w-sm">
                    <label for="delete_password" class="{{ $labelClass }}">Confirme com sua senha</label>
                    <input
                        id="delete_password"
                        name="delete_password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="{{ $inputClass }} focus:border-red-600 focus:ring-red-600/20"
                    >
                    @error('delete_password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="rounded-none bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600"
                >
                    Excluir conta
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
