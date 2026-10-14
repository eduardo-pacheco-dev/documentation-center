@php
    $createOpen = $errors->any() && old('modal') === 'create';
@endphp

<div
    id="radio-link-create-modal"
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="radio-link-create-modal-title"
    @if ($createOpen) data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="radio-link-create-modal-title" class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="arrows-right-left" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Novo radio link
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Preencha as etapas abaixo para cadastrar o enlace ponto a ponto.</p>
            </div>

            <button
                type="button"
                class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700"
                data-modal-close
            >
                <span class="sr-only">Fechar</span>
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        @if ($createOpen)
            <div
                data-modal-error
                class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300"
            >
                <p class="font-medium">Não foi possível criar o radio link. Corrija os itens destacados e tente novamente.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.radio-links.store') }}" class="mt-5" data-wizard novalidate>
            @csrf
            <input type="hidden" name="modal" value="create">

            <ol data-wizard-steps class="flex flex-wrap items-center gap-2" role="list"></ol>

            <div class="mt-5">
                <div data-wizard-panel data-wizard-title="Identificação">
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
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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

                <div data-wizard-panel data-wizard-title="Pontas" class="hidden">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="erb_a_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ponta A — ERB</label>
                            <select
                                name="erb_a_id"
                                id="erb_a_id"
                                required
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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

                <div data-wizard-panel data-wizard-title="Equipamentos" class="hidden">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="equipment_a" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Equipamento — Ponta A</label>
                            <input
                                type="text"
                                name="equipment_a"
                                id="equipment_a"
                                value="{{ old('equipment_a') }}"
                                placeholder="Ex.: Ericsson MINI-LINK 6363"
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >
                            @error('frequency')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div data-wizard-panel data-wizard-title="Configuração" class="hidden">
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
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
                                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                <button
                    type="button"
                    data-wizard-prev
                    class="hidden rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                >
                    Voltar
                </button>

                <div class="ml-auto flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200/70 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
                        data-modal-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        data-wizard-next
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        Próximo
                    </button>
                    <button
                        type="submit"
                        data-wizard-submit
                        class="hidden items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        <x-icon name="check" class="h-4 w-4" />
                        Criar radio link
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
