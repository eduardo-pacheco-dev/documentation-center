<x-layouts.admin title="Configurações">
    <h1 class="text-xl font-semibold">Configurações</h1>
    <p class="mt-1 text-sm text-gray-500">Gerencie a aparência, a segurança e a sua conta.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[14rem_minmax(0,1fr)]">
        <aside class="lg:sticky lg:top-6 lg:self-start">
            <nav
                x-data="{
                    active: 'aparencia',
                    sections: ['aparencia', 'senha', 'conta'],
                    init() {
                        const hash = window.location.hash.slice(1);

                        if (this.sections.includes(hash)) {
                            this.active = hash;
                        }

                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach((entry) => {
                                if (entry.isIntersecting) {
                                    this.active = entry.target.id;
                                }
                            });
                        }, { rootMargin: '-10% 0px -55% 0px' });

                        this.sections.forEach((id) => observer.observe(document.getElementById(id)));
                    },
                }"
                class="flex gap-1 overflow-x-auto rounded-lg border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:flex-col lg:overflow-visible lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none"
            >
                <a
                    href="#aparencia"
                    @click="active = 'aparencia'"
                    :class="active === 'aparencia'
                        ? 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white'
                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100'"
                    class="whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium"
                >
                    Aparência
                </a>

                <a
                    href="#senha"
                    @click="active = 'senha'"
                    :class="active === 'senha'
                        ? 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white'
                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100'"
                    class="whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium"
                >
                    Alterar senha
                </a>

                <a
                    href="#conta"
                    @click="active = 'conta'"
                    :class="active === 'conta'
                        ? 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-400'
                        : 'text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/15'"
                    class="whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium"
                >
                    Excluir conta
                </a>
            </nav>
        </aside>

        <div class="min-w-0 max-w-2xl space-y-6">
            <form
                id="aparencia"
                method="POST"
                action="{{ route('settings.theme.update') }}"
                class="scroll-mt-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                @csrf
                @method('PUT')

                <h2 class="text-sm font-semibold">Aparência</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Escolha como o painel será exibido para você.</p>

                <div class="mt-4">
                    <label for="theme-select" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tema</label>

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
                            class="flex w-full items-center justify-between gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        >
                            <span x-text="options[value].label"></span>
                            <x-icon name="chevron-up-down" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                        </button>

                        <ul
                            x-show="open"
                            x-transition
                            @click.outside="open = false"
                            role="listbox"
                            aria-label="Tema"
                            style="display: none;"
                            class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-800 dark:bg-gray-900"
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

                <button
                    type="submit"
                    class="mt-4 rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                >
                    Salvar aparência
                </button>
            </form>

            <form
                id="senha"
                method="POST"
                action="{{ route('settings.password.update') }}"
                class="scroll-mt-6 space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                @csrf
                @method('PUT')

                <div>
                    <h2 class="text-sm font-semibold">Alterar senha</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use uma senha forte que você não utiliza em outros serviços.</p>
                </div>

                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Senha atual</label>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nova senha</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirmar nova senha</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                >
                    Atualizar senha
                </button>
            </form>

            <form
                id="conta"
                method="POST"
                action="{{ route('settings.account.destroy') }}"
                class="scroll-mt-6 space-y-4 rounded-xl border border-red-200 bg-white p-5 shadow-sm dark:border-red-900 dark:bg-gray-900"
            >
                @csrf
                @method('DELETE')

                <div>
                    <h2 class="text-sm font-semibold text-red-700 dark:text-red-400">Excluir conta</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Sua conta será removida permanentemente, junto com seus links e arquivos. Essa ação não pode ser desfeita.
                    </p>
                </div>

                <div class="max-w-sm">
                    <label for="delete_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirme com sua senha</label>
                    <input
                        id="delete_password"
                        name="delete_password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:outline-none focus:ring-red-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                    @error('delete_password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600"
                    onclick="return confirm('Excluir sua conta permanentemente?')"
                >
                    Excluir conta
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
