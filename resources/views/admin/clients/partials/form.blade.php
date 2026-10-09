@php($client = $client ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $client?->name) }}"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('name')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="document" class="block text-sm font-medium text-gray-700 dark:text-gray-300">CPF/CNPJ</label>
        <input
            type="text"
            name="document"
            id="document"
            maxlength="20"
            value="{{ old('document', $client?->document) }}"
            placeholder="000.000.000-00"
            inputmode="numeric"
            data-mask="cpf-cnpj"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('document')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">E-mail</label>
        <input
            type="email"
            name="email"
            id="email"
            value="{{ old('email', $client?->email) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('email')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
        <input
            type="text"
            name="phone"
            id="phone"
            maxlength="30"
            value="{{ old('phone', $client?->phone) }}"
            inputmode="tel"
            placeholder="(00) 00000-0000"
            data-mask="phone"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('phone')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Site</label>
        <input
            type="url"
            name="website"
            id="website"
            value="{{ old('website', $client?->website) }}"
            placeholder="https://"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('website')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Endereço</span>
        <div class="mt-1 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="street" class="block text-xs text-gray-500 dark:text-gray-400">Rua</label>
                <input
                    type="text"
                    name="street"
                    id="street"
                    value="{{ old('street', $client?->street) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('street')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="number" class="block text-xs text-gray-500 dark:text-gray-400">Número</label>
                <input
                    type="text"
                    name="number"
                    id="number"
                    maxlength="20"
                    value="{{ old('number', $client?->number) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('number')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="complement" class="block text-xs text-gray-500 dark:text-gray-400">Complemento</label>
                <input
                    type="text"
                    name="complement"
                    id="complement"
                    value="{{ old('complement', $client?->complement) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('complement')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="neighborhood" class="block text-xs text-gray-500 dark:text-gray-400">Bairro</label>
                <input
                    type="text"
                    name="neighborhood"
                    id="neighborhood"
                    value="{{ old('neighborhood', $client?->neighborhood) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('neighborhood')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="zip" class="block text-xs text-gray-500 dark:text-gray-400">CEP</label>
                <input
                    type="text"
                    name="zip"
                    id="zip"
                    maxlength="9"
                    value="{{ old('zip', $client?->zip) }}"
                    placeholder="00000-000"
                    inputmode="numeric"
                    data-mask="cep"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                @error('zip')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="state" class="block text-xs text-gray-500 dark:text-gray-400">UF</label>
                <select
                    name="state"
                    id="state"
                    data-state-select
                    data-selected="{{ old('state', $client?->state) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                    <option value="">Carregando...</option>
                </select>
                @error('state')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="city" class="block text-xs text-gray-500 dark:text-gray-400">Cidade</label>
                <select
                    name="city"
                    id="city"
                    data-city-select
                    data-selected="{{ old('city', $client?->city) }}"
                    disabled
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <option value="">Selecione a UF primeiro</option>
                </select>
                @error('city')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select
            name="status"
            id="status"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            @foreach (\App\Enums\ClientStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $client?->status?->value ?? 'active') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
        <textarea
            name="notes"
            id="notes"
            rows="3"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >{{ old('notes', $client?->notes) }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
        @enderror
    </div>
</div>

@once
    <script>
        (() => {
            const maskCpfCnpj = (value) => {
                const digits = value.replace(/\D/g, '').slice(0, 14);

                if (digits.length <= 11) {
                    if (digits.length > 9) return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6, 9)}-${digits.slice(9)}`;
                    if (digits.length > 6) return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6)}`;
                    if (digits.length > 3) return `${digits.slice(0, 3)}.${digits.slice(3)}`;

                    return digits;
                }

                if (digits.length > 12) return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5, 8)}/${digits.slice(8, 12)}-${digits.slice(12)}`;
                if (digits.length > 8) return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5, 8)}/${digits.slice(8)}`;
                if (digits.length > 5) return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5)}`;

                return `${digits.slice(0, 2)}.${digits.slice(2)}`;
            };

            const maskPhone = (value) => {
                const digits = value.replace(/\D/g, '').slice(0, 11);

                if (digits.length === 0) return '';
                if (digits.length <= 2) return `(${digits}`;
                if (digits.length <= 6) return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
                if (digits.length <= 10) return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;

                return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
            };

            const maskCep = (value) => {
                const digits = value.replace(/\D/g, '').slice(0, 8);

                return digits.length > 5 ? `${digits.slice(0, 5)}-${digits.slice(5)}` : digits;
            };

            const masks = {
                'cpf-cnpj': maskCpfCnpj,
                phone: maskPhone,
                cep: maskCep,
            };

            const format = (input) => {
                const formatter = masks[input.dataset.mask];

                if (!formatter) return;

                const formatted = formatter(input.value);

                if (formatted !== input.value) {
                    input.value = formatted;
                }
            };

            document.querySelectorAll('[data-mask]').forEach(format);

            document.addEventListener('input', (event) => {
                const input = event.target.closest('[data-mask]');

                if (input) {
                    format(input);
                }
            });
        })();
    </script>
@endonce

@once
    <script>
        (() => {
            const stateSelect = document.querySelector('[data-state-select]');
            const citySelect = document.querySelector('[data-city-select]');

            if (!stateSelect || !citySelect) return;

            const statesUrl = 'https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome';
            const citiesUrl = (uf) => `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios?orderBy=nome`;

            const streetInput = document.getElementById('street');
            const neighborhoodInput = document.getElementById('neighborhood');
            const zipInput = document.getElementById('zip');

            const selectedState = stateSelect.dataset.selected || '';
            const selectedCity = citySelect.dataset.selected || '';

            const fillOptions = (select, options, placeholder) => {
                select.innerHTML = '';

                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = placeholder;
                select.append(empty);

                options.forEach((option) => {
                    const element = document.createElement('option');
                    element.value = option.value;
                    element.textContent = option.label;
                    select.append(element);
                });
            };

            const selectState = (uf) => {
                stateSelect.value = uf;

                if (stateSelect.value !== uf) {
                    const legacy = document.createElement('option');
                    legacy.value = uf;
                    legacy.textContent = uf;
                    stateSelect.append(legacy);
                    stateSelect.value = uf;
                }
            };

            const loadCities = async (uf, city = '') => {
                citySelect.disabled = true;
                fillOptions(citySelect, [], uf ? 'Carregando...' : 'Selecione a UF primeiro');

                if (!uf) return;

                try {
                    const response = await fetch(citiesUrl(uf));
                    const cities = await response.json();

                    fillOptions(citySelect, cities.map((item) => ({ value: item.nome, label: item.nome })), 'Selecione...');
                    citySelect.disabled = false;

                    if (city) {
                        citySelect.value = city;
                    }
                } catch (error) {
                    fillOptions(citySelect, [], 'Erro ao carregar cidades');
                }
            };

            stateSelect.addEventListener('change', () => loadCities(stateSelect.value));

            const statesReady = (async () => {
                try {
                    const response = await fetch(statesUrl);
                    const states = await response.json();

                    fillOptions(stateSelect, states.map((item) => ({ value: item.sigla, label: `${item.nome} (${item.sigla})` })), 'Selecione...');

                    if (!selectedState) return;

                    selectState(selectedState);
                    await loadCities(selectedState, selectedCity);
                } catch (error) {
                    fillOptions(stateSelect, [], 'Erro ao carregar UFs');
                }
            })();

            const lookupZip = async () => {
                if (!zipInput) return;

                const zip = zipInput.value.replace(/\D/g, '');

                if (zip.length !== 8) return;

                await statesReady;

                try {
                    const response = await fetch(`https://viacep.com.br/ws/${zip}/json/`);
                    const data = await response.json();

                    if (!data || data.erro) return;

                    if (streetInput && data.logradouro) {
                        streetInput.value = data.logradouro;
                    }

                    if (neighborhoodInput && data.bairro) {
                        neighborhoodInput.value = data.bairro;
                    }

                    if (data.uf) {
                        selectState(data.uf);
                        await loadCities(data.uf, data.localidade || '');
                    }
                } catch (error) {
                    // Mantém o preenchimento manual quando o CEP não é encontrado.
                }
            };

            if (zipInput) {
                zipInput.addEventListener('blur', lookupZip);
            }
        })();
    </script>
@endonce
