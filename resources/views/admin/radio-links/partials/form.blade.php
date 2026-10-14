@php
    $radioLink = $radioLink ?? null;
    $wizard = $wizard ?? false;
    $inputClass = 'mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $panelClass = fn (int $index): string => 'grid gap-5 sm:grid-cols-2'.($wizard && $index > 0 ? ' hidden' : '');
@endphp

<div class="space-y-5" @if ($wizard) data-wizard data-wizard-labels='["Identificação","Pontas","Configuração"]' @endif>
    <div @if ($wizard) data-wizard-step @endif class="{{ $panelClass(0) }}">
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código</label>
            <input
                type="text"
                name="code"
                id="code"
                value="{{ old('code', $radioLink?->code) }}"
                required
                placeholder="Ex.: RL-00001"
                class="{{ $inputClass }}"
            >
            @error('code')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select name="status" id="status" class="{{ $inputClass }}">
                @foreach (\App\Enums\RadioLinkStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected(old('status', $radioLink?->status?->value ?? 'planned') === $statusOption->value)>
                        {{ $statusOption->label() }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div @if ($wizard) data-wizard-step @endif class="{{ $panelClass(1) }}">
        <div>
            <label for="erb_a_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ponta A — ERB</label>
            <select name="erb_a_id" id="erb_a_id" required class="{{ $inputClass }}">
                <option value="" disabled @selected(old('erb_a_id', $radioLink?->erb_a_id) === null)>Selecione a ERB da ponta A</option>
                @foreach ($erbs as $erb)
                    <option value="{{ $erb->getKey() }}" @selected((string) old('erb_a_id', $radioLink?->erb_a_id) === (string) $erb->getKey())>
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
            <select name="erb_b_id" id="erb_b_id" required class="{{ $inputClass }}">
                <option value="" disabled @selected(old('erb_b_id', $radioLink?->erb_b_id) === null)>Selecione a ERB da ponta B</option>
                @foreach ($erbs as $erb)
                    <option value="{{ $erb->getKey() }}" @selected((string) old('erb_b_id', $radioLink?->erb_b_id) === (string) $erb->getKey())>
                        {{ $erb->code }} — {{ $erb->name }}
                    </option>
                @endforeach
            </select>
            @error('erb_b_id')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="equipment_a" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Equipamento — Ponta A</label>
            <input
                type="text"
                name="equipment_a"
                id="equipment_a"
                value="{{ old('equipment_a', $radioLink?->equipment_a) }}"
                placeholder="Ex.: Ericsson MINI-LINK 6363"
                class="{{ $inputClass }}"
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
                value="{{ old('equipment_b', $radioLink?->equipment_b) }}"
                placeholder="Ex.: Cambium PTP 820"
                class="{{ $inputClass }}"
            >
            @error('equipment_b')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-xs text-gray-500 sm:col-span-2 dark:text-gray-400">
            A distância entre as pontas é calculada automaticamente pelas coordenadas das ERBs selecionadas.
        </p>
    </div>

    <div @if ($wizard) data-wizard-step @endif class="{{ $panelClass(2) }}">
        <div>
            <label for="frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Frequência (GHz)</label>
            <input
                type="text"
                inputmode="decimal"
                name="frequency"
                id="frequency"
                value="{{ old('frequency', $radioLink?->frequency) }}"
                placeholder="Ex.: 23.500"
                class="{{ $inputClass }}"
            >
            @error('frequency')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="bandwidth" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Largura de banda (MHz)</label>
            <input
                type="text"
                inputmode="decimal"
                name="bandwidth"
                id="bandwidth"
                value="{{ old('bandwidth', $radioLink?->bandwidth) }}"
                placeholder="Ex.: 40"
                class="{{ $inputClass }}"
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
                value="{{ old('capacity', $radioLink?->capacity) }}"
                placeholder="Ex.: 1000"
                class="{{ $inputClass }}"
            >
            @error('capacity')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="polarization" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Polarização</label>
            <select name="polarization" id="polarization" class="{{ $inputClass }}">
                <option value="" @selected(old('polarization', $radioLink?->polarization?->value) === null)>Não informada</option>
                @foreach (\App\Enums\RadioLinkPolarization::cases() as $polarization)
                    <option value="{{ $polarization->value }}" @selected(old('polarization', $radioLink?->polarization?->value) === $polarization->value)>
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
                class="{{ $inputClass }}"
            >{{ old('notes', $radioLink?->notes) }}</textarea>
            @error('notes')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
