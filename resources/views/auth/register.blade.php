<x-layouts.app title="Criar conta">
    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-8 shadow-sm">
        <h1 class="text-lg font-semibold">Criar conta</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre-se para acessar o painel.</p>

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nome</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    autocomplete="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">E-mail</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    value="{{ old('email') }}"
                    required
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
                    autocomplete="new-password"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirmar senha</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
            </div>

            <button
                type="submit"
                class="w-full rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                Criar conta
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            Já tem uma conta?
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 dark:text-indigo-300 hover:underline">Entrar</a>
        </p>
    </div>
</x-layouts.app>
