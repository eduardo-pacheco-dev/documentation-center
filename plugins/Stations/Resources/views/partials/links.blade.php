@php
    $cardClass = 'rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]';
    $inputClass = 'h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:focus:bg-white/5';
@endphp

<section class="{{ $cardClass }} p-5">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Enlaces</h2>
        <x-ui.badge tone="gray">{{ $station->linksAsEndA->count() + $station->linksAsEndB->count() }}</x-ui.badge>
    </div>

    @if ($stationOptions->isEmpty())
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
            Cadastre outra station para criar enlaces.
        </p>
    @else
        <form method="POST" action="{{ route('stations.links.store', $station) }}" class="mt-4 grid gap-2 sm:grid-cols-6">
            @csrf

            <div class="sm:col-span-3">
                <label for="station_b_id" class="sr-only">Station da outra ponta</label>
                <select id="station_b_id" name="station_b_id" class="{{ $inputClass }}" required>
                    <option value="">Station da outra ponta...</option>
                    @foreach ($stationOptions as $option)
                        <option value="{{ $option->getKey() }}" @selected(old('station_b_id') == $option->getKey())>
                            {{ $option->code }} — {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                @error('station_b_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="link_code" class="sr-only">Código</label>
                <input id="link_code" name="code" type="text" value="{{ old('code') }}" placeholder="Código (automático)" class="{{ $inputClass }}">
                @error('code') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-1">
                <x-ui.action type="submit" icon="plus" class="h-10 w-full">
                    Criar
                </x-ui.action>
            </div>
        </form>
    @endif

    @if ($station->linksAsEndA->isEmpty() && $station->linksAsEndB->isEmpty())
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Nenhum enlace cadastrado.</p>
    @else
        <ul class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/80 dark:divide-white/10 dark:border-white/10">
            @foreach ($station->linksAsEndA->concat($station->linksAsEndB) as $link)
                @php
                    $other = $link->station_a_id === $station->getKey() ? $link->stationB : $link->stationA;
                @endphp

                <li class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-icon name="arrows-right-left" class="h-4 w-4 text-gray-400" />

                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $link->code }}</p>

                        <x-ui.badge :tone="$link->status->tone()">{{ $link->status->label() }}</x-ui.badge>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            com
                            <a href="{{ route('stations.show', $other) }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                {{ $other?->code ?? '—' }}
                            </a>
                        </p>

                        <form
                            method="POST"
                            action="{{ route('stations.links.destroy', [$station, $link]) }}"
                            class="ml-auto"
                            data-confirm="Remover o enlace {{ $link->code }}?"
                            data-confirm-title="Remover enlace"
                            data-confirm-label="Remover"
                        >
                            @csrf
                            @method('DELETE')

                            <x-ui.action type="submit" size="sm" variant="danger" icon="trash">
                                Remover
                            </x-ui.action>
                        </form>
                    </div>

                    <details class="mt-2">
                        <summary class="cursor-pointer text-xs font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-300">
                            Editar enlace
                        </summary>

                        <form method="POST" action="{{ route('stations.links.update', [$station, $link]) }}" class="mt-2 grid gap-2 sm:grid-cols-3">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="link-status-{{ $link->getKey() }}" class="block text-xs text-gray-500 dark:text-gray-400">Status</label>
                                <select id="link-status-{{ $link->getKey() }}" name="status" class="{{ $inputClass }}">
                                    @foreach ($linkStatuses as $status)
                                        <option value="{{ $status->value }}" @selected($link->status === $status)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="link-frequency-{{ $link->getKey() }}" class="block text-xs text-gray-500 dark:text-gray-400">Frequência (GHz)</label>
                                <input id="link-frequency-{{ $link->getKey() }}" name="frequency" type="text" value="{{ $link->frequency }}" class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label for="link-capacity-{{ $link->getKey() }}" class="block text-xs text-gray-500 dark:text-gray-400">Capacidade (Mbps)</label>
                                <input id="link-capacity-{{ $link->getKey() }}" name="capacity" type="text" value="{{ $link->capacity }}" class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label for="link-equipment-a-{{ $link->getKey() }}" class="block text-xs text-gray-500 dark:text-gray-400">Equipamento A</label>
                                <input id="link-equipment-a-{{ $link->getKey() }}" name="equipment_a" type="text" value="{{ $link->equipment_a }}" class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label for="link-equipment-b-{{ $link->getKey() }}" class="block text-xs text-gray-500 dark:text-gray-400">Equipamento B</label>
                                <input id="link-equipment-b-{{ $link->getKey() }}" name="equipment_b" type="text" value="{{ $link->equipment_b }}" class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="link-notes-{{ $link->getKey() }}" class="block text-xs text-gray-500 dark:text-gray-400">Observações</label>
                                <textarea id="link-notes-{{ $link->getKey() }}" name="notes" rows="2" class="mt-1 block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200">{{ $link->notes }}</textarea>
                            </div>

                            <div class="sm:col-span-3">
                                <x-ui.action type="submit" size="sm" icon="check">
                                    Salvar enlace
                                </x-ui.action>
                            </div>
                        </form>
                    </details>
                </li>
            @endforeach
        </ul>
    @endif
</section>
