<x-layouts.app title="Entrar">
    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-8 shadow-sm">
        <h1 class="text-lg font-semibold">Entrar</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Acesse o painel administrativo.</p>

        @if (session('status'))
            <div class="mt-4 rounded-md border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-3 py-2 text-sm text-green-800 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">E-mail</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Senha</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 dark:text-indigo-300 focus:ring-indigo-500">
                Lembrar de mim
            </label>

            <button
                type="submit"
                class="w-full rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                Entrar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            Não tem uma conta?
            <a href="{{ route('register') }}" class="font-medium text-indigo-600 dark:text-indigo-300 hover:underline">Cadastre-se</a>
        </p>
    </div>
</x-layouts.app>
