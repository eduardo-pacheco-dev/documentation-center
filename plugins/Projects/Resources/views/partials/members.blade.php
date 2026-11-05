<section id="members" class="mt-8 hidden" data-project-panel>
    <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
        <x-icon name="user-plus" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
        Membros
    </h2>

    @if (in_array($role?->value, ['owner'], true))
        <form method="POST" action="{{ route('admin.projects.members.store', $project) }}" class="mt-3 flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            @csrf

            <div class="grow">
                <label for="member-email" class="block text-xs font-medium text-gray-500 dark:text-gray-400">E-mail do usuário</label>
                <input type="email" name="email" id="member-email" required placeholder="usuario@exemplo.com" class="mt-1 block w-full max-w-sm rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
            </div>

            <div>
                <label for="member-role" class="block text-xs font-medium text-gray-500 dark:text-gray-400">Papel</label>
                <select name="role" id="member-role" class="mt-1 block rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    <option value="editor" @selected(old('role', 'editor') === 'editor')>Editor</option>
                    <option value="viewer" @selected(old('role') === 'viewer')>Visualizador</option>
                </select>
            </div>

            <button
                type="submit"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-3 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="user-plus" class="h-4 w-4" />
                Adicionar
            </button>
        </form>
    @endif

    <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Nome</th>
                    <th class="px-4 py-3 font-medium">E-mail</th>
                    <th class="px-4 py-3 font-medium">Papel</th>
                    @if ($role?->value === 'owner')
                        <th class="px-4 py-3 text-right font-medium">Ações</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <tr>
                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $project->user->name }}</td>
                    <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ $project->user->email }}</td>
                    <td class="px-4 py-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 dark:bg-indigo-500/15 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">Proprietário</span>
                    </td>
                    @if ($role?->value === 'owner')
                        <td class="px-4 py-2"></td>
                    @endif
                </tr>
                @forelse ($members as $member)
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $member->user?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ $member->user?->email ?? '—' }}</td>
                        <td class="px-4 py-2">
                            @if ($role?->value === 'owner')
                                <form method="POST" action="{{ route('admin.projects.members.update', [$project, $member]) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-2 py-1 text-xs shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                                        <option value="editor" @selected($member->role === 'editor')>Editor</option>
                                        <option value="viewer" @selected($member->role === 'viewer')>Visualizador</option>
                                    </select>
                                    <button type="submit" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Salvar</button>
                                </form>
                            @else
                                {{ $member->role === 'editor' ? 'Editor' : 'Visualizador' }}
                            @endif
                        </td>
                        @if ($role?->value === 'owner')
                            <td class="px-4 py-2 text-right">
                                <form method="POST" action="{{ route('admin.projects.members.destroy', [$project, $member]) }}" data-confirm-delete>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400" aria-label="Remover membro">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-gray-500 dark:text-gray-400" colspan="4">Nenhum membro além do proprietário.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
