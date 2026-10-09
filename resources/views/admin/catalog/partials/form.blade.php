@php($catalogItem = $catalogItem ?? null)

<div class="grid gap-5 sm:grid-cols-2" x-data="{ type: '{{ old('type', $catalogItem?->type?->value ?? 'product') }}' }">
    <div>
        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
        <select
            name="type"
            id="type"
            required
            x-on:change="type = $event.target.value"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected(old('type', $catalogItem?->type?->value ?? 'product') === $type->value)>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
        @error('type')
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
            @foreach (\App\Enums\CatalogItemStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $catalogItem?->status?->value ?? 'active') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $catalogItem?->name) }}"
            required
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('name')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código / SKU</label>
        <input
            type="text"
            name="code"
            id="code"
            maxlength="60"
            value="{{ old('code', $catalogItem?->code) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('code')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unidade</label>
        <input
            type="text"
            name="unit"
            id="unit"
            maxlength="20"
            value="{{ old('unit', $catalogItem?->unit) }}"
            placeholder="un, kg, m², h..."
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('unit')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preço de venda (R$)</label>
        <input
            type="number"
            name="price"
            id="price"
            step="0.01"
            min="0"
            value="{{ old('price', $catalogItem?->price) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('price')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custo (R$)</label>
        <input
            type="number"
            name="cost"
            id="cost"
            step="0.01"
            min="0"
            value="{{ old('cost', $catalogItem?->cost) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('cost')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="type === 'product'" style="display: none;">
        <label for="stock_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estoque</label>
        <input
            type="number"
            name="stock_quantity"
            id="stock_quantity"
            step="0.01"
            min="0"
            value="{{ old('stock_quantity', $catalogItem?->stock_quantity) }}"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
        @error('stock_quantity')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
        <textarea
            name="description"
            id="description"
            rows="3"
            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >{{ old('description', $catalogItem?->description) }}</textarea>
        @error('description')
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
        >{{ old('notes', $catalogItem?->notes) }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
