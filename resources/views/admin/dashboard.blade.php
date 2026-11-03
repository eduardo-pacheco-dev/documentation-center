<x-layouts.dashboard title="Dashboard">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400 dark:text-gray-500">Visão geral</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $greeting }}</h1>
            <p class="mt-1.5 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                Bem-vindo ao sistema base. Gerencie sua conta, seus módulos e os plugins instalados.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 text-xs font-medium text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                <x-icon name="home" class="h-4 w-4" />
                {{ now()->locale('pt_BR')->translatedFormat('d \d\e M, Y') }}
            </span>
            <a
                href="{{ route('settings') }}"
                class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-gray-900 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
            >
                <x-icon name="cog-6-tooth" class="h-4 w-4" />
                Configurações
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @if ($counts['users'] !== null)
            <a
                href="{{ route('admin.users.index') }}"
                data-stat="usuarios"
                class="group relative overflow-hidden rounded-2xl border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.04)] transition duration-200 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-lg hover:shadow-gray-900/5 dark:border-white/10 dark:bg-[#171716] dark:hover:border-white/20"
            >
                <div class="flex items-start justify-between">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-300">
                        <x-icon name="users" class="h-5 w-5" />
                    </span>
                    <x-icon name="arrow-top-right-on-square" class="h-4 w-4 text-gray-300 transition group-hover:text-gray-500 dark:text-gray-600 dark:group-hover:text-gray-300" />
                </div>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ number_format($counts['users'], 0, ',', '.') }}</p>
                <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">Usuários</p>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Contas cadastradas</p>
            </a>
        @endif

        <a
            href="{{ route('settings') }}#plugins"
            data-stat="plugins-instalados"
            class="group relative overflow-hidden rounded-2xl border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.04)] transition duration-200 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-lg hover:shadow-gray-900/5 dark:border-white/10 dark:bg-[#171716] dark:hover:border-white/20"
        >
            <div class="flex items-start justify-between">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-300">
                    <x-icon name="squares-2x2" class="h-5 w-5" />
                </span>
                <x-icon name="arrow-top-right-on-square" class="h-4 w-4 text-gray-300 transition group-hover:text-gray-500 dark:text-gray-600 dark:group-hover:text-gray-300" />
            </div>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ number_format($counts['plugins'], 0, ',', '.') }}</p>
            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">Plugins instalados</p>
            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Extensões disponíveis</p>
        </a>

        <a
            href="{{ route('settings') }}#plugins"
            data-stat="plugins-ativos"
            class="group relative overflow-hidden rounded-2xl border border-gray-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.04)] transition duration-200 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-lg hover:shadow-gray-900/5 dark:border-white/10 dark:bg-[#171716] dark:hover:border-white/20"
        >
            <div class="flex items-start justify-between">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <x-icon name="cog-6-tooth" class="h-5 w-5" />
                </span>
                <x-icon name="arrow-top-right-on-square" class="h-4 w-4 text-gray-300 transition group-hover:text-gray-500 dark:text-gray-600 dark:group-hover:text-gray-300" />
            </div>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ number_format($counts['enabledPlugins'], 0, ',', '.') }}</p>
            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">Plugins ativos</p>
            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Carregados na aplicação</p>
        </a>
    </div>

    @if ($recentUsers->isNotEmpty())
        <section class="mt-4 overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04)] dark:border-white/10 dark:bg-[#171716]">
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
                        @foreach ($recentUsers as $recentUser)
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
</x-layouts.dashboard>
