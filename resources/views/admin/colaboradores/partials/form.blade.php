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
        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cargo</label>
        <input
            type="text"
            name="role"
            id="role"
            value="{{ old('role', $colaborador?->role) }}"
            placeholder="Ex.: Técnico N2"
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
        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone</label>
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

    <div class="sm:col-span-2">
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