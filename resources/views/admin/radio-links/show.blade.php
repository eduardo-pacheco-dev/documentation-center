<x-layouts.admin :title="$radioLink->code">
    @php
        $ghz = fn (?string $value): string => $value === null
            ? '—'
            : number_format((float) $value, 3, ',', '.').' GHz';
        $mhz = fn (?string $value): string => $value === null
            ? '—'
            : number_format((float) $value, 0, ',', '.').' MHz';
        $mbps = fn (?string $value): string => $value === null
            ? '—'
            : number_format((float) $value, 0, ',', '.').' Mbps';
        $km = fn (?float $value): string => $value === null
            ? '—'
            : number_format($value, 2, ',', '.').' km';
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="arrows-right-left" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                {{ $radioLink->code }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Enlace de {{ $radioLink->erbA?->code }} a {{ $radioLink->erbB?->code }}
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <x-radio-link-status :radio-link="$radioLink" />
                @if ($radioLink->polarization)
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $radioLink->polarization->label() }}</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.radio-links.edit', $radioLink) }}"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </a>

            <a
                href="{{ route('admin.radio-links.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Voltar
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Ponta A</h2>
            @if ($radioLink->erbA)
                <a href="{{ route('admin.erbs.show', $radioLink->erbA) }}" class="mt-3 block text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                    {{ $radioLink->erbA->code }} — {{ $radioLink->erbA->name }}
                </a>
            @else
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">ERB não encontrada.</p>
            @endif
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Equipamento: {{ $radioLink->equipment_a ?? 'Não informado' }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Ponta B</h2>
            @if ($radioLink->erbB)
                <a href="{{ route('admin.erbs.show', $radioLink->erbB) }}" class="mt-3 block text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                    {{ $radioLink->erbB->code }} — {{ $radioLink->erbB->name }}
                </a>
            @else
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">ERB não encontrada.</p>
            @endif
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Equipamento: {{ $radioLink->equipment_b ?? 'Não informado' }}
            </p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Parâmetros técnicos</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Frequência</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $ghz($radioLink->frequency) }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Largura de banda</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $mhz($radioLink->bandwidth) }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Capacidade</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $mbps($radioLink->capacity) }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Polarização</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $radioLink->polarization?->label() ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Distância do enlace</h2>
            <p class="mt-3 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $km($radioLink->distanceKm()) }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Calculada pelas coordenadas das ERBs das pontas A e B.
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:col-span-2 lg:col-span-1">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Observações</h2>
            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                {{ $radioLink->notes ?? 'Nenhuma observação registrada.' }}
            </p>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        Criado em {{ $radioLink->created_at->format('d/m/Y H:i') }}
        &middot;
        Atualizado em {{ $radioLink->updated_at->format('d/m/Y H:i') }}
    </div>
</x-layouts.admin>