<x-layouts.admin title="Usuários">
    @php
        $viewQuery = request()->query();
        unset($viewQuery['page']);
    @endphp

    <div class="flex items-center justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="users" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                Usuários
            </h1>
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

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
    <form
        method="GET"
        action="{{ route('admin.users.index') }}"
        class="flex flex-wrap items-center gap-2"
        data-search-form
    >
        @if (request('view'))
            <input type="hidden" name="view" value="{{ request('view') }}">
        @endif
        @if (request('sort'))
            <input type="hidden" name="sort" value="{{ request('sort') }}">
        @endif
        @if (request('direction'))
            <input type="hidden" name="direction" value="{{ request('direction') }}">
        @endif

        <div class="relative w-full max-w-sm">
            <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Buscar por nome ou e-mail..."
                class="block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                data-search-input
            >
        </div>

        <button
            type="submit"
            class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
        >
            Buscar
        </button>

        @if ($search !== '')
            <a
                href="{{ route('admin.users.index') }}"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
            >
                Limpar
            </a>
        @endif
    </form>

    <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Modo de visualização">
        @foreach (['table' => 'Tabela', 'cards' => 'Cards', 'compact' => 'Lista'] as $mode => $label)
            <a
                href="{{ route('admin.users.index', array_merge($viewQuery, ['view' => $mode])) }}"
                data-view-toggle="{{ $mode }}"
                class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm {{ $view === $mode ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
            >
                <x-icon :name="$mode === 'table' ? 'table-cells' : ($mode === 'cards' ? 'squares-2x2' : 'bars-3')" class="h-4 w-4" />
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

    @if ($view === 'table')
        <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            @error('user')
                <p class="border-b border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-5 py-3 text-sm text-red-700 dark:text-red-300">{{ $message }}</p>
            @enderror

            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <x-sort-header column="name" label="Nome" class="px-5 py-3" />
                        <x-sort-header column="email" label="E-mail" class="px-5 py-3" />
                        <x-sort-header column="is_admin" label="Perfil" class="px-5 py-3" />
                        <x-sort-header column="created_at" label="Criado em" icon="clock" class="px-5 py-3" />
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

                                @unless ($user->is_active)
                                    <span class="ml-1 inline-flex rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Inativo
                                    </span>
                                @endunless
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <x-user-actions-dropdown :user="$user" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="5">
                                @if ($search !== '')
                                    Nenhum usuário encontrado para "{{ $search }}".
                                @else
                                    <span class="inline-flex items-center gap-2">
                                        <x-icon name="users" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                        Nenhum usuário cadastrado até o momento.
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif ($view === 'cards')
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($users as $user)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>

                        <x-user-actions-dropdown :user="$user" />
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $user->is_admin ? 'bg-indigo-100 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300' }}">
                            {{ $user->is_admin ? 'Admin' : 'Usuário' }}
                        </span>

                        @unless ($user->is_active)
                            <span class="inline-flex rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                Inativo
                            </span>
                        @endunless
                    </div>

                    <p class="mt-3 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <x-icon name="clock" class="h-3.5 w-3.5" />
                        Criado em {{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}
                    </p>
                </div>
            @empty
                <div class="col-span-full rounded-lg border border-dashed border-gray-300 px-5 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    @if ($search !== '')
                        Nenhum usuário encontrado para "{{ $search }}".
                    @else
                        Nenhum usuário cadastrado até o momento.
                    @endif
                </div>
            @endforelse
        </div>
    @else
        <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @forelse ($users as $user)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    </div>

                    <div class="hidden items-center gap-2 sm:flex">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $user->is_admin ? 'bg-indigo-100 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300' }}">
                            {{ $user->is_admin ? 'Admin' : 'Usuário' }}
                        </span>

                        @unless ($user->is_active)
                            <span class="inline-flex rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                Inativo
                            </span>
                        @endunless
                    </div>

                    <span class="hidden text-xs text-gray-500 md:inline dark:text-gray-400">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</span>

                    <x-user-actions-dropdown :user="$user" />
                </div>
            @empty
                <p class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    @if ($search !== '')
                        Nenhum usuário encontrado para "{{ $search }}".
                    @else
                        Nenhum usuário cadastrado até o momento.
                    @endif
                </p>
            @endforelse
        </div>
    @endif

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <div
        id="user-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="user-modal-title"
        @if ($errors->hasAny(['name', 'email', 'password']) && old('modal') === 'create') data-open @endif
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
                <input type="hidden" name="modal" value="create">

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

    <div
        id="edit-user-modal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="edit-user-modal-title"
        data-action-template="{{ route('admin.users.update', ['user' => '__ID__']) }}"
        @if ($errors->hasAny(['name', 'email', 'password']) && old('modal') === 'edit') data-open @endif
    >
        <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white dark:bg-gray-900 p-5 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="edit-user-modal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Editar usuário</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atualize os dados da conta. Deixe a senha vazia para mantê-la.</p>
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
                id="edit-user-form"
                method="POST"
                action=""
                class="mt-5 space-y-4"
            >
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" value="{{ old('user_id') }}">
                <input type="hidden" name="modal" value="edit">

                <div>
                    <label for="edit-name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nome</label>
                    <input
                        id="edit-name"
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
                    <label for="edit-email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">E-mail</label>
                    <input
                        id="edit-email"
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
                    <label for="edit-password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nova senha (opcional)</label>
                    <input
                        id="edit-password"
                        name="password"
                        type="password"
                        class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="edit-password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirmar nova senha</label>
                    <input
                        id="edit-password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                    >
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin')) class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 dark:text-indigo-300 focus:ring-indigo-500">
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
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);

            if (params.has('view')) {
                return;
            }

            const savedView = localStorage.getItem('users-view');

            if (!['table', 'cards', 'compact'].includes(savedView)) {
                return;
            }

            params.set('view', savedView);
            window.location.replace(window.location.pathname + '?' + params.toString());
        })();

        document.querySelectorAll('[data-view-toggle]').forEach((link) => {
            link.addEventListener('click', () => {
                localStorage.setItem('users-view', link.dataset.viewToggle);
            });
        });

        const closeDropdowns = (except) => {
            document.querySelectorAll('[data-dropdown-menu]:not(.hidden)').forEach((menu) => {
                if (menu !== except) {
                    menu.classList.add('hidden');
                    menu.previousElementSibling.setAttribute('aria-expanded', 'false');
                }
            });
        };

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-dropdown-toggle]');

            if (toggle) {
                const menu = toggle.nextElementSibling;
                const willOpen = menu.classList.contains('hidden');
                closeDropdowns(willOpen ? menu : null);
                menu.classList.toggle('hidden', !willOpen);
                toggle.setAttribute('aria-expanded', String(willOpen));
                return;
            }

            if (!event.target.closest('[data-dropdown-menu]')) {
                closeDropdowns();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDropdowns();
            }
        });

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
                closeDropdowns();
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

        const editModal = document.getElementById('edit-user-modal');
        const editForm = document.getElementById('edit-user-form');

        document.querySelectorAll('[data-user-edit]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                editForm.action = editModal.dataset.actionTemplate.replace('__ID__', trigger.dataset.userId);
                editForm.elements.namedItem('name').value = trigger.dataset.userName;
                editForm.elements.namedItem('email').value = trigger.dataset.userEmail;
                editForm.elements.namedItem('password').value = '';
                editForm.elements.namedItem('password_confirmation').value = '';
                editForm.elements.namedItem('is_admin').checked = trigger.dataset.userAdmin === '1';
                openModal(editModal);
            });
        });

        if (editModal?.hasAttribute('data-open') && editForm.elements.namedItem('user_id').value) {
            editForm.action = editModal.dataset.actionTemplate.replace('__ID__', editForm.elements.namedItem('user_id').value);
            openModal(editModal);
        }

        const searchForm = document.querySelector('[data-search-form]');
        const searchInput = document.querySelector('[data-search-input]');

        if (searchForm && searchInput) {
            let searchTimer;

            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => searchForm.submit(), 400);
            });

            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    clearTimeout(searchTimer);
                }
            });

            if (searchInput.value) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }
        }
    </script>
</x-layouts.admin>
