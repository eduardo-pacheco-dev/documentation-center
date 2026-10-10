@php($radioLink = $radioLink ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código</label>
        <input
            type="text"
            name="code"
            id="code"
            value="{{ old('code', $radioLink?->code) }}"
            required
            placeholder="Ex.: RL-00001"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('code')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="erb_a_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ponta A — ERB</label>
        <select
            name="erb_a_id"
            id="erb_a_id"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
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
        <select
            name="erb_b_id"
            id="erb_b_id"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
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

    <div class="sm:col-span-2">
        <p class="text-xs text-gray-500 dark:text-gray-400">A distância entre as pontas é calculada automaticamente pelas coordenadas das ERBs selecionadas.</p>
    </div>

    <div>
        <label for="equipment_a" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Equipamento — Ponta A</label>
        <input
            type="text"
            name="equipment_a"
            id="equipment_a"
            value="{{ old('equipment_a', $radioLink?->equipment_a) }}"
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
            value="{{ old('equipment_b', $radioLink?->equipment_b) }}"
            placeholder="Ex.: Ericsson MINI-LINK 6363"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('equipment_b')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Frequência (GHz)</label>
        <input
            type="text"
            inputmode="decimal"
            name="frequency"
            id="frequency"
            value="{{ old('frequency', $radioLink?->frequency) }}"
            placeholder="Ex.: 23.500"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
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
            value="{{ old('capacity', $radioLink?->capacity) }}"
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

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select
            name="status"
            id="status"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            @foreach (\App\Enums\RadioLinkStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $radioLink?->status?->value ?? 'planned') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status')
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
        >{{ old('notes', $radioLink?->notes) }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>