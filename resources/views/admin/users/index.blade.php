<x-layouts.admin title="Usuários">
    <h1 class="text-xl font-semibold">Usuários</h1>
    <p class="mt-1 text-sm text-gray-500">Gerencie as contas da plataforma.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <form
            method="POST"
            action="{{ route('admin.users.store') }}"
            class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
        >
            @csrf

            <h2 class="text-sm font-semibold">Novo usuário</h2>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar senha</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="is_admin" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Tornar administrador
            </label>

            <button
                type="submit"
                class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
            >
                Criar usuário
            </button>
        </form>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:col-span-2">
            @error('user')
                <p class="border-b border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700">{{ $message }}</p>
            @enderror

            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nome</th>
                        <th class="px-5 py-3 font-medium">E-mail</th>
                        <th class="px-5 py-3 font-medium">Perfil</th>
                        <th class="px-5 py-3 font-medium">Criado em</th>
                        <th class="px-5 py-3 font-medium text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-5 py-3">{{ $user->name }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $user->is_admin ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $user->is_admin ? 'Admin' : 'Usuário' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                @unless ($user->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="text-sm font-medium text-red-600 hover:underline"
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
                            <td class="px-5 py-6 text-gray-500" colspan="5">Nenhum usuário encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-5 py-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-layouts.admin>
