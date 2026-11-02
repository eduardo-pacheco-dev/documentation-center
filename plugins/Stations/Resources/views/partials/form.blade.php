@php
    $inputClass = 'mt-1 block h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:placeholder:text-gray-500 dark:focus:bg-white/5';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
    $secondaryButton = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 text-sm font-semibold text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300';
@endphp

<div class="grid gap-5">
    <section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
        <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Identificação</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="site_id" class="{{ $labelClass }}">Site ID</label>
                <input id="site_id" name="site_id" type="text" value="{{ old('site_id', $station->site_id) }}" class="{{ $inputClass }}">
                @error('site_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="end_id" class="{{ $labelClass }}">End ID</label>
                <input id="end_id" name="end_id" type="text" value="{{ old('end_id', $station->end_id) }}" class="{{ $inputClass }}">
                @error('end_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="operator" class="{{ $labelClass }}">Operadora</label>
                <select id="operator" name="operator" class="{{ $inputClass }}">
                    <option value="">—</option>
                    @foreach ($operators as $operator)
                        <option value="{{ $operator->value }}" @selected(old('operator', $station->operator) === $operator->value)>{{ $operator->value }}</option>
                    @endforeach
                </select>
                @error('operator') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="technology" class="{{ $labelClass }}">Tecnologia</label>
                <select id="technology" name="technology" class="{{ $inputClass }}">
                    <option value="">—</option>
                    @foreach ($technologies as $technology)
                        <option value="{{ $technology->value }}" @selected(old('technology', $station->technology) === $technology->value)>{{ $technology->value }}</option>
                    @endforeach
                </select>
                @error('technology') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="status" class="{{ $labelClass }}">Status</label>
                <select id="status" name="status" class="{{ $inputClass }}">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $station->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('status') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="assigned_to" class="{{ $labelClass }}">Responsável (e-mail)</label>
                <input id="assigned_to" name="assigned_to" type="email" value="{{ old('assigned_to', $station->assigned_to) }}" class="{{ $inputClass }}">
                @error('assigned_to') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="nokia_smp_id" class="{{ $labelClass }}">Nokia SMP ID</label>
                <input id="nokia_smp_id" name="nokia_smp_id" type="text" value="{{ old('nokia_smp_id', $station->nokia_smp_id) }}" class="{{ $inputClass }}">
                @error('nokia_smp_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="service_order_id" class="{{ $labelClass }}">Service Order ID</label>
                <input id="service_order_id" name="service_order_id" type="text" value="{{ old('service_order_id', $station->service_order_id) }}" class="{{ $inputClass }}">
                @error('service_order_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="start_date" class="{{ $labelClass }}">Início</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $station->start_date?->toDateString()) }}" class="{{ $inputClass }}">
                @error('start_date') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="end_date" class="{{ $labelClass }}">Término</label>
                <input id="end_date" name="end_date" type="date" value="{{ old('end_date', $station->end_date?->toDateString()) }}" class="{{ $inputClass }}">
                @error('end_date') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Localização</h2>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="{{ $secondaryButton }}" data-station-zip-lookup>
                    <x-icon name="magnifying-glass" class="h-4 w-4" />
                    Buscar por CEP
                </button>

                <button type="button" class="{{ $secondaryButton }}" data-station-coordinate-lookup>
                    <x-icon name="map-pin" class="h-4 w-4" />
                    Buscar por coordenadas
                </button>
            </div>
        </div>

        <p class="mt-2 hidden text-sm text-gray-500 dark:text-gray-400" data-station-lookup-feedback></p>

        <div class="mt-4 grid gap-4 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <label for="zip" class="{{ $labelClass }}">CEP</label>
                <input id="zip" name="zip" type="text" value="{{ old('zip', $station->zip) }}" class="{{ $inputClass }}">
                @error('zip') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-4">
                <label for="street" class="{{ $labelClass }}">Logradouro</label>
                <input id="street" name="street" type="text" value="{{ old('street', $station->street) }}" class="{{ $inputClass }}">
                @error('street') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="number" class="{{ $labelClass }}">Número</label>
                <input id="number" name="number" type="text" value="{{ old('number', $station->number) }}" class="{{ $inputClass }}">
                @error('number') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-4">
                <label for="complement" class="{{ $labelClass }}">Complemento</label>
                <input id="complement" name="complement" type="text" value="{{ old('complement', $station->complement) }}" class="{{ $inputClass }}">
                @error('complement') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-3">
                <label for="neighborhood" class="{{ $labelClass }}">Bairro</label>
                <input id="neighborhood" name="neighborhood" type="text" value="{{ old('neighborhood', $station->neighborhood) }}" class="{{ $inputClass }}">
                @error('neighborhood') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="city" class="{{ $labelClass }}">Cidade</label>
                <input id="city" name="city" type="text" value="{{ old('city', $station->city) }}" class="{{ $inputClass }}">
                @error('city') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-1">
                <label for="state" class="{{ $labelClass }}">UF</label>
                <select id="state" name="state" class="{{ $inputClass }}">
                    <option value="">—</option>
                    @foreach ($states as $state)
                        <option value="{{ $state }}" @selected(old('state', $station->state) === $state)>{{ $state }}</option>
                    @endforeach
                </select>
                @error('state') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-3">
                <label for="latitude" class="{{ $labelClass }}">Latitude</label>
                <input id="latitude" name="latitude" type="text" value="{{ old('latitude', $station->latitude) }}" class="{{ $inputClass }}">
                @error('latitude') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-3">
                <label for="longitude" class="{{ $labelClass }}">Longitude</label>
                <input id="longitude" name="longitude" type="text" value="{{ old('longitude', $station->longitude) }}" class="{{ $inputClass }}">
                @error('longitude') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
        <h2 class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Observações</h2>

        <textarea id="notes" name="notes" rows="4" class="mt-1.5 block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 dark:focus:bg-white/5">{{ old('notes', $station->notes) }}</textarea>
        @error('notes') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </section>
</div>

<script>
    (function () {
        const feedback = document.querySelector('[data-station-lookup-feedback]');
        const field = (name) => document.querySelector(`[name="${name}"]`);

        const setFeedback = (message) => {
            if (!feedback) {
                return;
            }

            feedback.textContent = message;
            feedback.classList.toggle('hidden', !message);
        };

        const fill = (data) => {
            Object.entries(data).forEach(([key, value]) => {
                const input = field(key);

                if (input && value !== null && value !== '') {
                    input.value = value;
                }
            });
        };

        const request = async (url) => {
            setFeedback('Buscando endereço...');

            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                const payload = await response.json();

                if (!response.ok) {
                    setFeedback(payload.message || 'Endereço não encontrado.');
                    return;
                }

                fill(payload);
                setFeedback('Endereço preenchido.');
            } catch {
                setFeedback('Não foi possível consultar o endereço agora.');
            }
        };

        document.querySelector('[data-station-zip-lookup]')?.addEventListener('click', () => {
            const zip = field('zip')?.value?.trim();

            if (!zip) {
                setFeedback('Informe o CEP antes de buscar.');
                return;
            }

            request(`{{ route('stations.lookup.zip') }}?zip=${encodeURIComponent(zip)}`);
        });

        document.querySelector('[data-station-coordinate-lookup]')?.addEventListener('click', () => {
            const latitude = field('latitude')?.value?.trim();
            const longitude = field('longitude')?.value?.trim();

            if (!latitude || !longitude) {
                setFeedback('Informe latitude e longitude antes de buscar.');
                return;
            }

            request(`{{ route('stations.lookup.coordinates') }}?latitude=${encodeURIComponent(latitude)}&longitude=${encodeURIComponent(longitude)}`);
        });
    })();
</script>
