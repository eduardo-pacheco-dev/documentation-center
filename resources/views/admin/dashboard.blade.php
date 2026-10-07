<x-layouts.admin title="Dashboard">
    <h1 class="text-xl font-semibold">Dashboard</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visão geral da plataforma.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total de usuários</p>
            <p class="mt-2 text-2xl font-semibold">{{ $totalUsers }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Administradores</p>
            <p class="mt-2 text-2xl font-semibold">{{ $totalAdmins }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <p class="text-sm text-gray-500 dark:text-gray-400">Novos nos últimos 7 dias</p>
            <p class="mt-2 text-2xl font-semibold">{{ $signupsThisWeek }}</p>
        </div>
    </div>

    <section class="mt-8">
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Meus links</h2>
        <div class="mt-3 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total de links</p>
                <p class="mt-2 text-2xl font-semibold">{{ $totalLinks }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-sm text-gray-500 dark:text-gray-400">Links ativos</p>
                <p class="mt-2 text-2xl font-semibold">{{ $activeLinks }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-sm text-gray-500 dark:text-gray-400">Documentos recebidos</p>
                <p class="mt-2 text-2xl font-semibold">{{ $receivedDocuments }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-sm text-gray-500 dark:text-gray-400">Meus arquivos</p>
                <p class="mt-2 text-2xl font-semibold">{{ $totalFiles }}</p>
            </div>
        </div>
    </section>

    <section class="mt-8 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
        <div class="border-b border-gray-200 dark:border-gray-800 px-5 py-4">
            <h2 class="text-sm font-semibold">Cadastros recentes</h2>
        </div>

        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3 font-medium">Usuário</th>
                    <th class="px-5 py-3 font-medium">Cadastrado em</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($recentSignups as $signup)
                    <tr>
                        <td class="px-5 py-3">{{ $signup->name }}</td>
                        <td class="px-5 py-3 text-gray-500 dark:text-gray-400">
                            {{ $signup->created_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="2">Nenhum usuário cadastrado até o momento.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-layouts.admin>
