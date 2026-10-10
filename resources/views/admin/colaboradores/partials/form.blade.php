@php($colaborador = $colaborador ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $colaborador?->name) }}"
            required
            placeholder="Ex.: Ana Carvalho"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('name')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="contract_regime" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Regime de Contrato</label>
        <select
            name="contract_regime"
            id="contract_regime"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            <option value="">Selecione...</option>
            @foreach (\App\Enums\ContractRegime::cases() as $regime)
                <option value="{{ $regime->value }}" @selected(old('contract_regime', $colaborador?->contract_regime?->value) === $regime->value)>
                    {{ $regime->label() }}
                </option>
            @endforeach
        </select>
        @error('contract_regime')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="regional" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Regional</label>
        <input
            type="text"
            name="regional"
            id="regional"
            maxlength="50"
            value="{{ old('regional', $colaborador?->regional) }}"
            placeholder="Ex.: NO"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('regional')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="uf" class="block text-sm font-medium text-gray-700 dark:text-gray-300">UF</label>
        <select
            name="uf"
            id="uf"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            <option value="">Selecione...</option>
            @foreach (\App\Enums\Uf::cases() as $uf)
                <option value="{{ $uf->value }}" @selected(old('uf', $colaborador?->uf?->value) === $uf->value)>
                    {{ $uf->label() }} ({{ $uf->value }})
                </option>
            @endforeach
        </select>
        @error('uf')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="pis" class="block text-sm font-medium text-gray-700 dark:text-gray-300">PIS</label>
        <input
            type="text"
            name="pis"
            id="pis"
            maxlength="14"
            value="{{ old('pis', $colaborador?->pis) }}"
            placeholder="000.00000.00-0"
            inputmode="numeric"
            data-mask="pis"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('pis')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Função</label>
        <input
            type="text"
            name="role"
            id="role"
            value="{{ old('role', $colaborador?->role) }}"
            placeholder="Ex.: Técnico em telecomunicações"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('role')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="document" class="block text-sm font-medium text-gray-700 dark:text-gray-300">CPF</label>
        <input
            type="text"
            name="document"
            id="document"
            maxlength="14"
            value="{{ old('document', $colaborador?->document) }}"
            placeholder="000.000.000-00"
            inputmode="numeric"
            data-mask="cpf"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('document')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="cnpj" class="block text-sm font-medium text-gray-700 dark:text-gray-300">CNPJ</label>
        <input
            type="text"
            name="cnpj"
            id="cnpj"
            maxlength="18"
            value="{{ old('cnpj', $colaborador?->cnpj) }}"
            placeholder="00.000.000/0000-00"
            inputmode="numeric"
            data-mask="cnpj"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('cnpj')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Nascimento</label>
        <input
            type="date"
            name="birth_date"
            id="birth_date"
            value="{{ old('birth_date', $colaborador?->birth_date?->format('Y-m-d')) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('birth_date')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:col-span-2 sm:grid-cols-2">
        <div>
            <label for="rg" class="block text-sm font-medium text-gray-700 dark:text-gray-300">RG</label>
            <input
                type="text"
                name="rg"
                id="rg"
                maxlength="30"
                value="{{ old('rg', $colaborador?->rg) }}"
                placeholder="Ex.: 3062601"
                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            @error('rg')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="rg_issuer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Órgão Emissor</label>
            <input
                type="text"
                name="rg_issuer"
                id="rg_issuer"
                maxlength="50"
                value="{{ old('rg_issuer', $colaborador?->rg_issuer) }}"
                placeholder="Ex.: SSP/PA"
                class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            @error('rg_issuer')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="sm:col-span-2">
        <label for="mother_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome da Mãe</label>
        <input
            type="text"
            name="mother_name"
            id="mother_name"
            value="{{ old('mother_name', $colaborador?->mother_name) }}"
            placeholder="Ex.: Maria Lúcia da Costa Ferreira"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('mother_name')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contato</label>
        <input
            type="text"
            name="phone"
            id="phone"
            maxlength="30"
            value="{{ old('phone', $colaborador?->phone) }}"
            inputmode="tel"
            placeholder="(00) 00000-0000"
            data-mask="phone"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('phone')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">E-mail</label>
        <input
            type="email"
            name="email"
            id="email"
            value="{{ old('email', $colaborador?->email) }}"
            placeholder="Ex.: joao@empresa.com.br"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('email')
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
            @foreach (\App\Enums\ColaboradorStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $colaborador?->status?->value ?? 'active') === $status->value)>
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
        >{{ old('notes', $colaborador?->notes) }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

@once
    <script>
        (() => {
            const maskCpf = (value) => {
                const digits = value.replace(/\D/g, '').slice(0, 11);

                if (digits.length > 9) return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6, 9)}-${digits.slice(9)}`;
                if (digits.length > 6) return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6)}`;
                if (digits.length > 3) return `${digits.slice(0, 3)}.${digits.slice(3)}`;

                return digits;
            };

            const maskCnpj = (value) => {
                const digits = value.replace(/\D/g, '').slice(0, 14);

                if (digits.length > 12) return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5, 8)}/${digits.slice(8, 12)}-${digits.slice(12)}`;
                if (digits.length > 8) return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5, 8)}/${digits.slice(8)}`;
                if (digits.length > 5) return `${digits.slice(0, 2)}.${digits.slice(2, 5)}.${digits.slice(5)}`;
                if (digits.length > 2) return `${digits.slice(0, 2)}.${digits.slice(2)}`;

                return digits;
            };

            const maskPis = (value) => {
                const digits = value.replace(/\D/g, '').slice(0, 11);

                if (digits.length > 10) return `${digits.slice(0, 3)}.${digits.slice(3, 8)}.${digits.slice(8, 10)}-${digits.slice(10)}`;
                if (digits.length > 8) return `${digits.slice(0, 3)}.${digits.slice(3, 8)}.${digits.slice(8)}`;
                if (digits.length > 3) return `${digits.slice(0, 3)}.${digits.slice(3)}`;

                return digits;
            };

            const maskPhone = (value) => {
                const digits = value.replace(/\D/g, '').slice(0, 11);

                if (digits.length === 0) return '';
                if (digits.length <= 2) return `(${digits}`;
                if (digits.length <= 6) return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
                if (digits.length <= 10) return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;

                return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
            };

            const masks = {
                cpf: maskCpf,
                cnpj: maskCnpj,
                pis: maskPis,
                phone: maskPhone,
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