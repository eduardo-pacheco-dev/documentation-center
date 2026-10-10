<x-layouts.admin :title="$erb->code">
    @php
        $address = collect([
            trim(($erb->street ?? '').($erb->number ? ', '.$erb->number : '')),
            $erb->complement,
            $erb->neighborhood,
            trim(($erb->city ?? '').($erb->state ? '/'.$erb->state : '')),
            $erb->zip,
        ])->filter();
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="signal" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                {{ $erb->code }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $erb->name }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <x-erb-status :erb="$erb" />
                @if ($erb->operator)
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $erb->operator }}</span>
                @endif
                @if ($erb->technology)
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $erb->technology }}</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.erbs.edit', $erb) }}"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </a>

            <a
                href="{{ route('admin.erbs.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Voltar
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Localização</h2>
            <div class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                @forelse ($address as $line)
                    <p>{{ $line }}</p>
                @empty
                    <p>Endereço não informado.</p>
                @endforelse

                @if ($erb->latitude !== null && $erb->longitude !== null)
                    <p class="pt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format((float) $erb->latitude, 5, ',', '.') }},
                        {{ number_format((float) $erb->longitude, 5, ',', '.') }}
                    </p>
                    <a
                        href="https://www.google.com/maps?q={{ $erb->latitude }},{{ $erb->longitude }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                    >
                        <x-icon name="map-pin" class="h-3.5 w-3.5" />
                        Ver no mapa
                    </a>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Operadora e tecnologia</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Operadora</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $erb->operator ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Tecnologia</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $erb->technology ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                    <dd><x-erb-status :erb="$erb" /></dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Observações</h2>
            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                {{ $erb->notes ?? 'Nenhuma observação registrada.' }}
            </p>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Ordens de serviço</h2>
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ $erb->workOrders->count() }}
            </span>
        </div>

        @if ($erb->workOrders->isEmpty())
            <p class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Nenhuma ordem de serviço vinculada a esta ERB.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Número</th>
                            <th class="px-5 py-3 font-medium">Título</th>
                            <th class="px-5 py-3 font-medium">Cliente</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($erb->workOrders as $workOrder)
                            <tr>
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                        {{ $workOrder->number }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $workOrder->title }}</td>
                                <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $workOrder->client?->name ?? '—' }}</td>
                                <td class="px-5 py-3"><x-work-order-status :work-order="$workOrder" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <x-icon name="briefcase" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Projetos</h2>
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ $erb->projects->count() }}
            </span>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($erb->projects as $project)
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                    <a href="{{ route('admin.projects.show', $project) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                        {{ $project->name }}
                    </a>
                    <x-project-status :project="$project" />
                </div>
            @empty
                <p class="px-5 py-8 text-sm text-gray-500 dark:text-gray-400">Nenhum projeto vinculado a esta ERB.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <x-icon name="arrows-right-left" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Radio links</h2>
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ $erb->radioLinksAsEndA->count() + $erb->radioLinksAsEndB->count() }}
            </span>
        </div>

        @php
            $radioLinks = $erb->radioLinksAsEndA->merge($erb->radioLinksAsEndB)->sortByDesc('updated_at');
        @endphp

        @if ($radioLinks->isEmpty())
            <p class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">Nenhum radio link vinculado a esta ERB.</p>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($radioLinks as $radioLink)
                    @php
                        $otherEnd = $radioLink->erb_a_id === $erb->getKey() ? $radioLink->erbB : $radioLink->erbA;
                    @endphp
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <a href="{{ route('admin.radio-links.show', $radioLink) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">
                                {{ $radioLink->code }}
                            </a>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                com {{ $otherEnd?->code ?? 'ERB removida' }}
                                @if ($radioLink->frequency !== null)
                                    &middot; {{ number_format((float) $radioLink->frequency, 1, ',', '.') }} GHz
                                @endif
                            </span>
                        </div>
                        <x-radio-link-status :radio-link="$radioLink" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @include('admin.erbs.partials.documents', ['erb' => $erb])

    @include('admin.erbs.partials.comments', ['erb' => $erb])

    <div class="mt-4 rounded-xl border border-gray-200 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        Criado em {{ $erb->created_at->format('d/m/Y H:i') }}
        &middot;
        Atualizado em {{ $erb->updated_at->format('d/m/Y H:i') }}
    </div>
</x-layouts.admin>
