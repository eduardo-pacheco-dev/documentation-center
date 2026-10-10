@php
    $createSteps = [
        1 => ['code', 'status'],
        2 => ['erb_a_id', 'erb_b_id'],
        3 => ['equipment_a', 'equipment_b', 'frequency'],
        4 => ['bandwidth', 'capacity', 'polarization', 'notes'],
    ];

    $createStepLabels = [
        1 => 'Identificação',
        2 => 'Pontas',
        3 => 'Equipamentos',
        4 => 'Configuração',
    ];

    $createErrorStep = 1;

    foreach ($createSteps as $createStepNumber => $createStepFields) {
        if (collect($createStepFields)->filter(fn (string $field) => $errors->has($field))->isNotEmpty()) {
            $createErrorStep = $createStepNumber;
            break;
        }
    }
@endphp

<div
    data-create-modal
    @if ($errors->any()) data-open-on-load="true" @endif
    class="fixed inset-0 z-50 hidden flex overflow-y-auto p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="create-radio-link-title"
>
    <div class="fixed inset-0 bg-gray-950/50" data-create-modal-close></div>

    <div class="relative m-auto w-full max-w-2xl rounded-xl bg-white shadow-xl dark:bg-gray-900">
        <header class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <div>
                <h2 id="create-radio-link-title" class="text-base font-semibold">Novo radio link</h2>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Cadastre um enlace ponto a ponto entre duas ERBs.</p>
            </div>
            <button
                type="button"
                data-create-modal-close
                class="rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                aria-label="Fechar"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </header>

        <form method="POST" action="{{ route('admin.radio-links.store') }}" x-data="{ step: {{ $createErrorStep }} }">
            @csrf

            <nav class="border-b border-gray-200 px-5 py-3 dark:border-gray-800" aria-label="Etapas do formulário">
                <ol class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                    @foreach ($createSteps as $createStepNumber => $createStepFields)
                        <li>
                            <button type="button" @click="step = {{ $createStepNumber }}" class="flex items-center gap-1.5">
                                <span
                                    class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium"
                                    :class="step === {{ $createStepNumber }}
                                        ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                        : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'"
                                >{{ $createStepNumber }}</span>
                                <span
                                    class="hidden sm:inline"
                                    :class="step === {{ $createStepNumber }}
                                        ? 'font-medium text-gray-900 dark:text-gray-100'
                                        : 'text-gray-500 dark:text-gray-400'"
                                >{{ $createStepLabels[$createStepNumber] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ol>
            </nav>

            <div class="px-5 py-4">
                <div x-show="step === 1">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código</label>
                            <input
                                type="text"
                                name="code"
                                id="code"
                                value="{{ old('code') }}"
                                required
                                placeholder="Ex.: RL-00100"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                            @error('code')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                name="status"
                                id="status"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                                @foreach (\App\Enums\RadioLinkStatus::cases() as $statusOption)
                                    <option value="{{ $statusOption->value }}" @selected(old('status') === $statusOption->value)>
                                        {{ $statusOption->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div x-show="step === 2">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="erb_a_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ponta A — ERB</label>
                            <select
                                name="erb_a_id"
                                id="erb_a_id"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                                <option value="" disabled @selected(old('erb_a_id') === null)>Selecione a ERB da ponta A</option>
                                @foreach ($erbs as $erb)
                                    <option value="{{ $erb->getKey() }}" @selected((string) old('erb_a_id') === (string) $erb->getKey())>
                                        {{ $erb->code }} — {{ $erb->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('erb_a_id')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="erb_b_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ponta B — ERB</label>
                            <select
                                name="erb_b_id"
                                id="erb_b_id"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                                <option value="" disabled @selected(old('erb_b_id') === null)>Selecione a ERB da ponta B</option>
                                @foreach ($erbs as $erb)
                                    <option value="{{ $erb->getKey() }}" @selected((string) old('erb_b_id') === (string) $erb->getKey())>
                                        {{ $erb->code }} — {{ $erb->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('erb_b_id')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <p class="text-xs text-gray-500 sm:col-span-2 dark:text-gray-400">
                            A distância entre as pontas é calculada automaticamente pelas coordenadas das ERBs selecionadas.
                        </p>
                    </div>
                </div>

                <div x-show="step === 3">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="equipment_a" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Equipamento — Ponta A</label>
                            <input
                                type="text"
                                name="equipment_a"
                                id="equipment_a"
                                value="{{ old('equipment_a') }}"
                                placeholder="Ex.: Ericsson MINI-LINK 6363"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                            @error('equipment_a')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="equipment_b" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Equipamento — Ponta B</label>
                            <input
                                type="text"
                                name="equipment_b"
                                id="equipment_b"
                                value="{{ old('equipment_b') }}"
                                placeholder="Ex.: Cambium PTP 820"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                            @error('equipment_b')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Frequência (GHz)</label>
                            <input
                                type="text"
                                inputmode="decimal"
                                name="frequency"
                                id="frequency"
                                value="{{ old('frequency') }}"
                                placeholder="Ex.: 23.500"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                            @error('frequency')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div x-show="step === 4">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="bandwidth" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Largura de banda (MHz)</label>
                            <input
                                type="text"
                                inputmode="decimal"
                                name="bandwidth"
                                id="bandwidth"
                                value="{{ old('bandwidth') }}"
                                placeholder="Ex.: 40"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                            @error('bandwidth')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="capacity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacidade (Mbps)</label>
                            <input
                                type="text"
                                inputmode="decimal"
                                name="capacity"
                                id="capacity"
                                value="{{ old('capacity') }}"
                                placeholder="Ex.: 1000"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                            @error('capacity')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="polarization" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Polarização</label>
                            <select
                                name="polarization"
                                id="polarization"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >
                                <option value="" @selected(old('polarization') === null)>Não informada</option>
                                @foreach (\App\Enums\RadioLinkPolarization::cases() as $polarization)
                                    <option value="{{ $polarization->value }}" @selected(old('polarization') === $polarization->value)>
                                        {{ $polarization->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('polarization')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
                            <textarea
                                name="notes"
                                id="notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <footer class="flex items-center justify-between gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                <button
                    type="button"
                    @click="step = Math.max(1, step - 1)"
                    :disabled="step === 1"
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 disabled:cursor-not-allowed disabled:opacity-50 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                >
                    Voltar
                </button>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        data-create-modal-close
                        class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        @click="step = Math.min(4, step + 1)"
                        x-show="step < 4"
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        Próximo
                        <x-icon name="chevron-down" class="h-4 w-4 -rotate-90" />
                    </button>

                    <button
                        type="submit"
                        x-show="step === 4"
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Criar radio link
                    </button>
                </div>
            </footer>
        </form>
    </div>
</div>