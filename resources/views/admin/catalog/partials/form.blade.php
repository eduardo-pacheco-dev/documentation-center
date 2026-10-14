@php
    $catalogItem = $catalogItem ?? null;
    $wizard = $wizard ?? false;
    $fieldSuffix = $catalogItem?->getKey() ? '-'.$catalogItem->getKey() : '-novo';

    $unitOptions = ['un', 'kg', 'g', 't', 'l', 'ml', 'm', 'm²', 'm³', 'cm', 'mm', 'km', 'h', 'dia', 'mês', 'cx', 'pct', 'rolo', 'sc', 'par', 'kit', 'serviço'];
    $currentUnit = old('unit', $catalogItem?->unit);

    if ($currentUnit && ! in_array($currentUnit, $unitOptions, true)) {
        array_unshift($unitOptions, $currentUnit);
    }
@endphp

<div class="space-y-5" x-data="{ type: '{{ old('type', $catalogItem?->type?->value ?? 'product') }}' }" @if ($wizard) data-wizard @endif>
    <div @if ($wizard) data-wizard-step @endif class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="type{{ $fieldSuffix }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
            <select
                name="type"
                id="type{{ $fieldSuffix }}"
                required
                x-on:change="type = $event.target.value"
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
            <label for="status{{ $fieldSuffix }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select
                name="status"
                id="status{{ $fieldSuffix }}"
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
            <label for="name{{ $fieldSuffix }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
            <input
                type="text"
                name="name"
                id="name{{ $fieldSuffix }}"
                value="{{ old('name', $catalogItem?->name) }}"
                required
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            >
            @error('name')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div @if ($wizard) data-wizard-step @endif class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="unit{{ $fieldSuffix }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unidade</label>
            <x-searchable-select
                class="mt-1"
                name="unit"
                id="unit{{ $fieldSuffix }}"
                :selected="$currentUnit"
                placeholder="Selecione ou busque a unidade..."
                empty-message="Nenhuma unidade encontrada."
                :options="collect($unitOptions)->map(fn ($unitOption) => ['value' => $unitOption, 'label' => $unitOption])->all()"
            />
            @error('unit')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="price{{ $fieldSuffix }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preço de venda (R$)</label>
            <input
                type="number"
                name="price"
                id="price{{ $fieldSuffix }}"
                step="0.01"
                min="0"
                value="{{ old('price', $catalogItem?->price) }}"
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            >
            @error('price')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="cost{{ $fieldSuffix }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custo (R$)</label>
            <input
                type="number"
                name="cost"
                id="cost{{ $fieldSuffix }}"
                step="0.01"
                min="0"
                value="{{ old('cost', $catalogItem?->cost) }}"
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            >
            @error('cost')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="type === 'product'" style="display: none;">
            <label for="stock_quantity{{ $fieldSuffix }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estoque</label>
            <input
                type="number"
                name="stock_quantity"
                id="stock_quantity{{ $fieldSuffix }}"
                step="0.01"
                min="0"
                value="{{ old('stock_quantity', $catalogItem?->stock_quantity) }}"
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            >
            @error('stock_quantity')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div @if ($wizard) data-wizard-step @endif class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="description{{ $fieldSuffix }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
            <textarea
                name="description"
                id="description{{ $fieldSuffix }}"
                rows="3"
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            >{{ old('description', $catalogItem?->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="notes{{ $fieldSuffix }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
            <textarea
                name="notes"
                id="notes{{ $fieldSuffix }}"
                rows="3"
                class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            >{{ old('notes', $catalogItem?->notes) }}</textarea>
            @error('notes')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
