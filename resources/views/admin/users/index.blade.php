<x-layouts.admin title="Usuários">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Usuários</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie as contas da plataforma.</p>
        </div>

        <button
            type="button"
            data-modal-open="user-modal"
            class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
        >
            <x-icon name="plus" class="h-4 w-4" />
            Novo usuário
        </button>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        @error('user')
            <p class="border-b border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-5 py-3 text-sm text-red-700 dark:text-red-300">{{ $message }}</p>
        @enderror

        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3 font-medium">Nome</th>
                    <th class="px-5 py-3 font-medium">E-mail</th>
                    <th class="px-5 py-3 font-medium">Perfil</th>
                    <th class="px-5 py-3 font-medium">Criado em</th>
                    <th class="px-5 py-3 font-medium text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-5 py-3">{{ $user->name }}</td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $user->is_admin ? 'bg-indigo-100 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300' }}">
                                {{ $user->is_admin ? 'Admin' : 'Usuário' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            @unless ($user->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-sm font-medium text-red-600 dark:text-red-400 hover:underline"
                                        onclick="return confirm('Excluir o usuário {{ $user->name }}?')"
                                    >
                                        Excluir
                                    </button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="5">Nenhum usuário encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $users->links() }}
        </div>
    </div>

    <div
        id="user-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="user-modal-title"
        @if ($errors->hasAny(['name', 'email', 'password'])) data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white dark:bg-gray-900 p-5 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="user-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Novo usuário</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crie uma conta e defina se ela será administradora.</p>
                </div>

                <button
                    type="button"
                    class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700"
                    data-modal-close
                >
                    <span class="sr-only">Fechar</span>
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <form
                method="POST"
                action="{{ route('admin.users.store') }}"
                class="mt-5 space-y-4"
            >
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nome</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">E-mail</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Senha</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirmar senha</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" name="is_admin" value="1" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 dark:text-indigo-300 focus:ring-indigo-500">
                    Tornar administrador
                </label>

                <div class="flex justify-end gap-2 pt-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        Criar usuário
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const openModal = (modal) => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            const error = modal.querySelector('[data-modal-error]');
            if (error) {
                error.scrollIntoView({ block: 'center' });
            }
        };

        const closeModal = (modal) => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                openModal(document.getElementById(trigger.dataset.modalOpen));
            });
        });

        document.addEventListener('click', (event) => {
            const closer = event.target.closest('[data-modal-close]');
            if (closer) {
                closeModal(closer.closest('[role="dialog"]'));
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
            }
        });

        const userModal = document.getElementById('user-modal');
        if (userModal?.hasAttribute('data-open')) {
            openModal(userModal);
        }
    </script>
</x-layouts.admin>
