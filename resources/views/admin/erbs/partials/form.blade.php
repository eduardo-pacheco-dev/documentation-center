@php
    $erb = $erb ?? null;
    $wizard = $wizard ?? false;
    $panelClass = fn (int $index): string => trim('grid gap-5 sm:grid-cols-2 '.($wizard && $index > 0 ? 'hidden' : ''));
@endphp

<div data-wizard-panel data-wizard-title="Identificação" class="{{ $panelClass(0) }}">
    <div>
        <label for="site_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Site ID</label>
        <input
            type="text"
            name="site_id"
            id="site_id"
            value="{{ old('site_id', $erb?->site_id) }}"
            placeholder="Ex.: SITE-00001"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('site_id')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="end_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End ID</label>
        <input
            type="text"
            name="end_id"
            id="end_id"
            value="{{ old('end_id', $erb?->end_id) }}"
            placeholder="Ex.: PABLM_0001"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('end_id')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="operator" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Operadora</label>
        <select
            name="operator"
            id="operator"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            <option value="">Selecione a operadora</option>
            @foreach (\App\Enums\ErbOperator::cases() as $operatorOption)
                <option value="{{ $operatorOption->value }}" @selected(old('operator', $erb?->operator) === $operatorOption->value)>
                    {{ $operatorOption->label() }}
                </option>
            @endforeach
        </select>
        @error('operator')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="technology" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tecnologia</label>
        <select
            name="technology"
            id="technology"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            <option value="">Selecione a tecnologia</option>
            @foreach (\App\Enums\ErbTechnology::cases() as $technologyOption)
                <option value="{{ $technologyOption->value }}" @selected(old('technology', $erb?->technology) === $technologyOption->value)>
                    {{ $technologyOption->label() }}
                </option>
            @endforeach
        </select>
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
</div>

<div data-wizard-panel data-wizard-title="Localização" class="{{ $panelClass(1) }}">
    <div class="sm:col-span-2">
        @unless ($wizard)
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Localização</h2>
        @endunless
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Endereço e coordenadas da estação.</p>
    </div>

    <p
        data-erb-address-status
        class="sm:col-span-2 hidden text-xs text-gray-500 dark:text-gray-400"
        role="status"
        aria-live="polite"
    ></p>

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

<script>
    (function () {
        const script = document.currentScript;
        const form = script ? script.closest('form') : null;

        if (!form || form.dataset.addressLookupReady === 'true') {
            return;
        }

        form.dataset.addressLookupReady = 'true';

        const zipUrl = @json(route('admin.erbs.lookup.zip'));
        const coordinatesUrl = @json(route('admin.erbs.lookup.coordinates'));
        const digits = (value) => (value || '').replace(/\D/g, '');
        const field = (name) => form.querySelector('[name="' + name + '"]');
        const status = form.querySelector('[data-erb-address-status]');
        const fields = ['zip', 'street', 'number', 'complement', 'neighborhood', 'city', 'state'];
        let statusTimer = null;
        let coordinatesTimer = null;
        let lastZip = null;
        let lastCoordinates = null;

        const setStatus = (message, tone) => {
            if (!status) {
                return;
            }

            window.clearTimeout(statusTimer);
            status.textContent = message || '';
            status.classList.toggle('hidden', !message);
            status.classList.toggle('text-red-600', tone === 'error');
            status.classList.toggle('dark:text-red-400', tone === 'error');
            status.classList.toggle('text-gray-500', tone !== 'error');
            status.classList.toggle('dark:text-gray-400', tone !== 'error');

            if (message && tone === 'success') {
                statusTimer = window.setTimeout(() => setStatus(''), 4000);
            }
        };

        const fill = (data) => {
            fields.forEach((name) => {
                const input = field(name);
                const value = data[name];

                if (input && value !== undefined && value !== null && value !== '') {
                    input.value = value;
                }
            });
        };

        const request = async (url, params) => {
            const response = await fetch(url + '?' + new URLSearchParams(params).toString(), {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return null;
            }

            return response.json();
        };

        const lookupZip = async () => {
            const zipInput = field('zip');

            if (!zipInput) {
                return;
            }

            const value = digits(zipInput.value);

            if (value.length !== 8) {
                lastZip = null;

                return;
            }

            if (value === lastZip) {
                return;
            }

            lastZip = value;
            setStatus('Buscando endereço...', 'info');

            try {
                const data = await request(zipUrl, { zip: value });

                if (!data) {
                    lastZip = null;
                    setStatus('CEP não encontrado.', 'error');

                    return;
                }

                fill(data);
                setStatus('Endereço preenchido a partir do CEP.', 'success');
            } catch (error) {
                lastZip = null;
                setStatus('Não foi possível buscar o CEP.', 'error');
            }
        };

        const lookupCoordinates = async () => {
            const latitudeInput = field('latitude');
            const longitudeInput = field('longitude');

            if (!latitudeInput || !longitudeInput) {
                return;
            }

            const latitude = latitudeInput.value.trim();
            const longitude = longitudeInput.value.trim();

            if (latitude === '' || longitude === '' || Number.isNaN(Number(latitude)) || Number.isNaN(Number(longitude))) {
                lastCoordinates = null;

                return;
            }

            const key = latitude + ',' + longitude;

            if (key === lastCoordinates) {
                return;
            }

            lastCoordinates = key;
            setStatus('Buscando endereço...', 'info');

            try {
                const data = await request(coordinatesUrl, { latitude: latitude, longitude: longitude });

                if (!data) {
                    lastCoordinates = null;
                    setStatus('Não foi possível localizar o endereço para as coordenadas.', 'error');

                    return;
                }

                fill(data);
                setStatus('Endereço preenchido a partir das coordenadas.', 'success');
            } catch (error) {
                lastCoordinates = null;
                setStatus('Não foi possível buscar o endereço.', 'error');
            }
        };

        const scheduleCoordinates = () => {
            window.clearTimeout(coordinatesTimer);
            coordinatesTimer = window.setTimeout(lookupCoordinates, 600);
        };

        const zipInput = field('zip');

        if (zipInput) {
            zipInput.addEventListener('blur', lookupZip);
            zipInput.addEventListener('change', lookupZip);
        }

        ['latitude', 'longitude'].forEach((name) => {
            const input = field(name);

            if (input) {
                input.addEventListener('blur', scheduleCoordinates);
                input.addEventListener('change', scheduleCoordinates);
            }
        });
    })();
</script>
