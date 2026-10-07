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

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-3 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 dark:border-gray-800 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-500/10">
                        <input
                            type="radio"
                            name="theme"
                            value="system"
                            @checked(old('theme', $user->theme) === 'system')
                            class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                        >
                        <span>
                            <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Sistema</span>
                            <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Segue as preferências do seu dispositivo</span>
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-3 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 dark:border-gray-800 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-500/10">
                        <input
                            type="radio"
                            name="theme"
                            value="light"
                            @checked(old('theme', $user->theme) === 'light')
                            class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                        >
                        <span>
                            <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Claro</span>
                            <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Sempre com fundo claro</span>
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-3 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 dark:border-gray-800 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-500/10">
                        <input
                            type="radio"
                            name="theme"
                            value="dark"
                            @checked(old('theme', $user->theme) === 'dark')
                            class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                        >
                        <span>
                            <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Escuro</span>
                            <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Sempre com fundo escuro</span>
                        </span>
                    </label>
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
