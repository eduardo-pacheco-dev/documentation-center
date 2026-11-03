@props([
    'group',
    'prefix' => '',
    'station' => null,
    'inputClass' => '',
    'labelClass' => '',
])

@php
    $inputId = fn (string $name): string => $prefix !== '' ? "{$prefix}-{$name}" : $name;
    $value = fn (string $name): ?string => old($name, $station?->{$name});
    $estadoAtual = mb_strtoupper((string) $value('estado'));

    $fieldMeta = collect($group['fields'])->keyBy('name');
    $labelFor = fn (string $name): string => $fieldMeta[$name]['label'] ?? $name;
    $placeholderFor = fn (string $name): ?string => $fieldMeta[$name]['placeholder'] ?? null;

    $cepDigits = preg_replace('/\D/', '', (string) $value('cep'));
    $cepExibicao = strlen($cepDigits) === 8
        ? substr($cepDigits, 0, 5).'-'.substr($cepDigits, 5)
        : (string) $value('cep');

    $estados = config('stations.estados', []);
@endphp

<div
    x-data="{
        loading: false,
        message: '',
        succeeded: false,
        searched: '',
        async lookup() {
            const input = this.$refs.cep;
            const digits = input.value.replace(/\D/g, '');

            if (digits.length !== 8) {
                this.message = 'Informe os 8 dígitos do CEP.';
                this.succeeded = false;
                input.focus();

                return;
            }

            this.loading = true;
            this.message = '';
            this.searched = digits;

            try {
                const response = await fetch(`https://viacep.com.br/ws/${digits}/json/`, {
                    headers: { Accept: 'application/json' },
                });

                if (! response.ok) {
                    throw new Error('Falha na consulta');
                }

                const data = await response.json();

                if (data.erro) {
                    this.message = 'CEP não encontrado. Confira o número ou preencha o endereço manualmente.';
                    this.succeeded = false;

                    return;
                }

                this.fill('logradouro', data.logradouro);
                this.fill('bairro', data.bairro);
                this.fill('municipio', data.localidade);
                this.fill('estado', data.uf);
                this.fill('complemento', data.complemento, true);

                this.message = `Endereço preenchido automaticamente para ${data.localidade}/${data.uf}.`;
                this.succeeded = true;
            } catch (error) {
                this.message = 'Não foi possível consultar o CEP agora. Preencha o endereço manualmente.';
                this.succeeded = false;
            } finally {
                this.loading = false;
            }
        },
        fill(name, value, onlyWhenEmpty = false) {
            const field = this.$refs[name];

            if (! field || ! value) {
                return;
            }

            if (onlyWhenEmpty && field.value) {
                return;
            }

            field.value = value;
        },
        mask(event) {
            const digits = event.target.value.replace(/\D/g, '').slice(0, 8);

            event.target.value = digits.length > 5 ? `${digits.slice(0, 5)}-${digits.slice(5)}` : digits;

            if (digits.length !== 8) {
                this.message = '';
                this.succeeded = false;

                return;
            }

            if (digits !== this.searched) {
                this.lookup();
            }
        },
    }"
    class="mt-3 space-y-4"
>
    <div class="grid gap-4 sm:grid-cols-6">
        <div class="sm:col-span-3">
            <label for="{{ $inputId('cep') }}" class="{{ $labelClass }}">{{ $labelFor('cep') }}</label>

            <div class="flex items-start gap-2">
                <div class="relative min-w-0 flex-1">
                    <input
                        id="{{ $inputId('cep') }}"
                        name="cep"
                        type="text"
                        x-ref="cep"
                        value="{{ $cepExibicao }}"
                        inputmode="numeric"
                        maxlength="9"
                        placeholder="{{ $placeholderFor('cep') }}"
                        autocomplete="postal-code"
                        x-on:input="mask($event)"
                        x-on:keydown.enter.prevent="lookup()"
                        @error('cep') aria-invalid="true" @enderror
                        class="{{ $inputClass }} pr-9"
                    >

                    <span
                        x-cloak
                        x-show="loading"
                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-indigo-500 dark:text-indigo-400"
                    >
                        <x-icon name="arrow-path" class="h-4 w-4 animate-spin" />
                    </span>
                </div>

                <x-ui.action
                    type="button"
                    variant="secondary"
                    icon="magnifying-glass"
                    class="mt-1.5 shrink-0"
                    x-on:click="lookup()"
                    x-bind:disabled="loading"
                >
                    Buscar
                </x-ui.action>
            </div>

            @error('cep')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col justify-end sm:col-span-3">
            <p class="text-xs" aria-live="polite">
                <span x-cloak x-show="! message" class="text-gray-500 dark:text-gray-400">
                    Digite o CEP para preencher o endereço automaticamente.
                </span>
                <span
                    x-cloak
                    x-show="message"
                    x-text="message"
                    x-bind:class="succeeded ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                ></span>
            </p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-6">
        <div class="sm:col-span-6">
            <label for="{{ $inputId('logradouro') }}" class="{{ $labelClass }}">{{ $labelFor('logradouro') }}</label>
            <input
                id="{{ $inputId('logradouro') }}"
                name="logradouro"
                type="text"
                x-ref="logradouro"
                value="{{ $value('logradouro') }}"
                placeholder="{{ $placeholderFor('logradouro') }}"
                autocomplete="address-line1"
                @error('logradouro') aria-invalid="true" @enderror
                class="{{ $inputClass }}"
            >

            @error('logradouro')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-6">
        <div class="sm:col-span-2">
            <label for="{{ $inputId('numero') }}" class="{{ $labelClass }}">{{ $labelFor('numero') }}</label>
            <input
                id="{{ $inputId('numero') }}"
                name="numero"
                type="text"
                value="{{ $value('numero') }}"
                placeholder="{{ $placeholderFor('numero') }}"
                @error('numero') aria-invalid="true" @enderror
                class="{{ $inputClass }}"
            >

            @error('numero')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-4">
            <label for="{{ $inputId('complemento') }}" class="{{ $labelClass }}">{{ $labelFor('complemento') }}</label>
            <input
                id="{{ $inputId('complemento') }}"
                name="complemento"
                type="text"
                x-ref="complemento"
                value="{{ $value('complemento') }}"
                placeholder="{{ $placeholderFor('complemento') }}"
                autocomplete="address-line2"
                @error('complemento') aria-invalid="true" @enderror
                class="{{ $inputClass }}"
            >

            @error('complemento')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-6">
        <div class="sm:col-span-2">
            <label for="{{ $inputId('bairro') }}" class="{{ $labelClass }}">{{ $labelFor('bairro') }}</label>
            <input
                id="{{ $inputId('bairro') }}"
                name="bairro"
                type="text"
                x-ref="bairro"
                value="{{ $value('bairro') }}"
                placeholder="{{ $placeholderFor('bairro') }}"
                @error('bairro') aria-invalid="true" @enderror
                class="{{ $inputClass }}"
            >

            @error('bairro')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="{{ $inputId('municipio') }}" class="{{ $labelClass }}">{{ $labelFor('municipio') }}</label>
            <input
                id="{{ $inputId('municipio') }}"
                name="municipio"
                type="text"
                x-ref="municipio"
                value="{{ $value('municipio') }}"
                placeholder="{{ $placeholderFor('municipio') }}"
                autocomplete="address-level2"
                @error('municipio') aria-invalid="true" @enderror
                class="{{ $inputClass }}"
            >

            @error('municipio')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="{{ $inputId('estado') }}" class="{{ $labelClass }}">{{ $labelFor('estado') }}</label>
            <select
                id="{{ $inputId('estado') }}"
                name="estado"
                x-ref="estado"
                autocomplete="address-level1"
                @error('estado') aria-invalid="true" @enderror
                class="{{ $inputClass }}"
            >
                <option value="">UF</option>
                @foreach ($estados as $uf => $nome)
                    <option value="{{ $uf }}" @selected($estadoAtual === $uf)>{{ $uf }} — {{ $nome }}</option>
                @endforeach
            </select>

            @error('estado')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
