@if ($users->isNotEmpty())
    <section class="rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
        <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Usuários recentes</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Últimas contas criadas</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                Ver todos
                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:border-white/10 dark:bg-white/[0.02] dark:text-gray-500">
                        <th scope="col" class="px-5 py-2.5 font-semibold">Nome</th>
                        <th scope="col" class="px-3 py-2.5 font-semibold">E-mail</th>
                        <th scope="col" class="hidden px-3 py-2.5 font-semibold sm:table-cell">Perfil</th>
                        <th scope="col" class="hidden px-5 py-2.5 text-right font-semibold md:table-cell">Criado em</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($users as $recentUser)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.03]">
                            <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $recentUser->full_name }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $recentUser->email }}</td>
                            <td class="hidden px-3 py-3 text-sm text-gray-500 sm:table-cell dark:text-gray-400">
                                {{ $recentUser->is_admin ? 'Administrador' : 'Usuário' }}
                            </td>
                            <td class="hidden whitespace-nowrap px-5 py-3 text-right text-xs text-gray-400 md:table-cell dark:text-gray-500">
                                {{ $recentUser->created_at?->translatedFormat('d M Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif
