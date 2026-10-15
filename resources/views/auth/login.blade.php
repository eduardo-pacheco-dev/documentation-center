<x-layouts.app title="Entrar">
    <div class="relative overflow-hidden rounded-3xl border border-gray-200/70 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 via-violet-500 to-sky-500"></div>
        <div class="absolute -top-40 right-[-20%] h-80 w-80 rounded-full bg-indigo-500/10 blur-3xl dark:bg-indigo-500/20"></div>
        <div class="absolute -bottom-40 left-[-20%] h-80 w-80 rounded-full bg-violet-500/10 blur-3xl dark:bg-violet-500/20"></div>

        <div class="relative p-8 sm:p-10">
            <div class="flex flex-col items-center text-center">
                <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 shadow-sm ring-1 ring-indigo-600/10 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-400/20">
                    <x-icon name="document-text" class="h-7 w-7" />
                </span>
                <h1 class="mt-6 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Entrar no painel</h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Gerencie sua documentação com segurança e agilidade.</p>
            </div>

            @if (session('status'))
                <noscript>
                    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-300">
                        {{ session('status') }}
                    </div>
                </noscript>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">E-mail</label>
                    <div class="relative mt-1">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="block w-full rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:placeholder:text-gray-500 dark:focus:border-indigo-400 dark:focus:ring-indigo-400/30"
                        />
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Senha</label>
                    <div class="relative mt-1">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="block w-full rounded-2xl border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:placeholder:text-gray-500 dark:focus:border-indigo-400 dark:focus:ring-indigo-400/30"
                        />
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between gap-4">
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm transition focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-0 dark:border-gray-700 dark:bg-gray-900 dark:text-indigo-400 dark:focus:ring-indigo-400/40"
                        />
                        Lembrar de mim
                    </label>
                    <span class="text-sm text-gray-400 dark:text-gray-500">Entre com suas credenciais</span>
                </div>

                <button
                    type="submit"
                    class="group relative inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                >
                    <span>Entrar</span>
                    <x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                </button>
            </form>

            @if (Route::has('register'))
                <p class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Não tem uma conta?
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">Cadastre-se</a>
                </p>
            @else
                <p class="mt-8 text-center text-xs text-gray-400 dark:text-gray-500">
                    Acesso restrito ao painel interno.
                </p>
            @endif
        </div>
    </div>
</x-layouts.app>
