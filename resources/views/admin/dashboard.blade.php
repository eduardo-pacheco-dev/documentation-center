<x-layouts.admin title="Dashboard">
    <h1 class="text-xl font-semibold">Dashboard</h1>
    <p class="mt-1 text-sm text-gray-500">Visão geral da plataforma.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total de usuários</p>
            <p class="mt-2 text-2xl font-semibold">{{ $totalUsers }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Administradores</p>
            <p class="mt-2 text-2xl font-semibold">{{ $totalAdmins }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Novos nos últimos 7 dias</p>
            <p class="mt-2 text-2xl font-semibold">{{ $signupsThisWeek }}</p>
        </div>
    </div>

    <section class="mt-8">
        <h2 class="text-sm font-semibold text-gray-700">Meus links</h2>
        <div class="mt-3 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total de links</p>
                <p class="mt-2 text-2xl font-semibold">{{ $totalLinks }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Links ativos</p>
                <p class="mt-2 text-2xl font-semibold">{{ $activeLinks }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Documentos recebidos</p>
                <p class="mt-2 text-2xl font-semibold">{{ $receivedDocuments }}</p>
            </div>
        </div>
    </section>

    <section class="mt-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="text-sm font-semibold">Cadastros recentes</h2>
        </div>

        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Usuário</th>
                    <th class="px-5 py-3 font-medium">Cadastrado em</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($recentSignups as $signup)
                    <tr>
                        <td class="px-5 py-3">{{ $signup->name }}</td>
                        <td class="px-5 py-3 text-gray-500">
                            {{ $signup->created_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-6 text-gray-500" colspan="2">Nenhum usuário cadastrado até o momento.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-layouts.admin>
