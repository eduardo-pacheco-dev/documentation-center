@php($erb = $erb ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código</label>
        <input
            type="text"
            name="code"
            id="code"
            value="{{ old('code', $erb?->code) }}"
            required
            placeholder="Ex.: ERB-00001"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('code')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $erb?->name) }}"
            required
            placeholder="Ex.: ERB Centro"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('name')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="operator" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Operadora</label>
        <input
            type="text"
            name="operator"
            id="operator"
            value="{{ old('operator', $erb?->operator) }}"
            placeholder="Ex.: Vivo"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('operator')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="technology" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tecnologia</label>
        <input
            type="text"
            name="technology"
            id="technology"
            value="{{ old('technology', $erb?->technology) }}"
            placeholder="Ex.: 4G/5G"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('technology')
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
            @foreach (\App\Enums\ErbStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $erb?->status?->value ?? 'active') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Localização</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Endereço e coordenadas da estação.</p>
    </div>

    <div class="sm:col-span-2">
        <label for="street" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Logradouro</label>
        <input
            type="text"
            name="street"
            id="street"
            value="{{ old('street', $erb?->street) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('street')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número</label>
        <input
            type="text"
            name="number"
            id="number"
            value="{{ old('number', $erb?->number) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('number')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="complement" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Complemento</label>
        <input
            type="text"
            name="complement"
            id="complement"
            value="{{ old('complement', $erb?->complement) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('complement')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="neighborhood" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bairro</label>
        <input
            type="text"
            name="neighborhood"
            id="neighborhood"
            value="{{ old('neighborhood', $erb?->neighborhood) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('neighborhood')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cidade</label>
        <input
            type="text"
            name="city"
            id="city"
            value="{{ old('city', $erb?->city) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('city')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">UF</label>
        <input
            type="text"
            name="state"
            id="state"
            value="{{ old('state', $erb?->state) }}"
            maxlength="2"
            size="2"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm uppercase shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('state')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="zip" class="block text-sm font-medium text-gray-700 dark:text-gray-300">CEP</label>
        <input
            type="text"
            name="zip"
            id="zip"
            value="{{ old('zip', $erb?->zip) }}"
            placeholder="00000-000"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('zip')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Latitude</label>
        <input
            type="text"
            inputmode="decimal"
            name="latitude"
            id="latitude"
            value="{{ old('latitude', $erb?->latitude) }}"
            placeholder="Ex.: -23.5505200"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('latitude')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Longitude</label>
        <input
            type="text"
            inputmode="decimal"
            name="longitude"
            id="longitude"
            value="{{ old('longitude', $erb?->longitude) }}"
            placeholder="Ex.: -46.6333080"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('longitude')
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
        >{{ old('notes', $erb?->notes) }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
