<x-layouts.admin title="Perfil">
    <h1 class="text-xl font-semibold">Perfil</h1>
    <p class="mt-1 text-sm text-gray-500">Atualize suas informações pessoais.</p>

    <div class="mt-6 max-w-2xl space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-4">
                <img
                    src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D8ABC&color=fff&size=64"
                    alt="{{ $user->name }}"
                    class="h-16 w-16 rounded-full"
                >
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate text-lg font-medium">{{ $user->name }}</h2>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $user->is_admin ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $user->is_admin ? 'Administrador' : 'Usuário' }}
                        </span>
                    </div>
                    <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Membro desde {{ $user->created_at?->format('d/m/Y') ?? '—' }}
                    </p>
                </div>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('profile.update') }}"
            class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-sm font-semibold">Informações pessoais</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Seu nome e e-mail aparecem no painel e nos links que você cria.</p>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nome</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $user->name) }}"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
                    value="{{ old('email', $user->email) }}"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
            >
                Salvar alterações
            </button>
        </form>
    </div>
</x-layouts.admin>
