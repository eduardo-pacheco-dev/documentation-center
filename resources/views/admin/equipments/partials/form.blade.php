@php
    $equipment = $equipment ?? null;
    $statuses = $statuses ?? \App\Enums\EquipmentStatus::cases();
    $fieldSuffix = $equipment?->getKey() ? '-'.$equipment->getKey() : '-novo';

    $inputClass = 'mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $errorClass = 'mt-1 text-xs text-red-600 dark:text-red-400';
@endphp

<div class="space-y-5">
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name{{ $fieldSuffix }}" class="{{ $labelClass }}">Nome</label>
            <input
                type="text"
                name="name"
                id="name{{ $fieldSuffix }}"
                value="{{ old('name', $equipment?->name) }}"
                required
                class="{{ $inputClass }}"
            >
            @error('name')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="code{{ $fieldSuffix }}" class="{{ $labelClass }}">Código</label>
            <input
                type="text"
                name="code"
                id="code{{ $fieldSuffix }}"
                value="{{ old('code', $equipment?->code) }}"
                placeholder="Gerado automaticamente"
                class="{{ $inputClass }}"
            >
            @error('code')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category{{ $fieldSuffix }}" class="{{ $labelClass }}">Categoria</label>
            <input
                type="text"
                name="category"
                id="category{{ $fieldSuffix }}"
                value="{{ old('category', $equipment?->category) }}"
                placeholder="Ex.: Rádio, antena, fonte..."
                list="equipment-categories{{ $fieldSuffix }}"
                class="{{ $inputClass }}"
            >
            <datalist id="equipment-categories{{ $fieldSuffix }}">
                @foreach (['Rádio', 'Antena', 'Fonte', 'Switch', 'Roteador', 'Bateria', 'Cabo', 'Outro'] as $categoryOption)
                    <option value="{{ $categoryOption }}"></option>
                @endforeach
            </datalist>
            @error('category')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="manufacturer{{ $fieldSuffix }}" class="{{ $labelClass }}">Fabricante</label>
            <input
                type="text"
                name="manufacturer"
                id="manufacturer{{ $fieldSuffix }}"
                value="{{ old('manufacturer', $equipment?->manufacturer) }}"
                class="{{ $inputClass }}"
            >
            @error('manufacturer')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="model{{ $fieldSuffix }}" class="{{ $labelClass }}">Modelo</label>
            <input
                type="text"
                name="model"
                id="model{{ $fieldSuffix }}"
                value="{{ old('model', $equipment?->model) }}"
                class="{{ $inputClass }}"
            >
            @error('model')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status{{ $fieldSuffix }}" class="{{ $labelClass }}">Status</label>
            <select name="status" id="status{{ $fieldSuffix }}" class="{{ $inputClass }}">
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected(old('status', $equipment?->status?->value ?? 'active') === $statusOption->value)>
                        {{ $statusOption->label() }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="description{{ $fieldSuffix }}" class="{{ $labelClass }}">Descrição</label>
            <textarea
                name="description"
                id="description{{ $fieldSuffix }}"
                rows="3"
                class="{{ $inputClass }}"
            >{{ old('description', $equipment?->description) }}</textarea>
            @error('description')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="notes{{ $fieldSuffix }}" class="{{ $labelClass }}">Observações</label>
            <textarea
                name="notes"
                id="notes{{ $fieldSuffix }}"
                rows="3"
                class="{{ $inputClass }}"
            >{{ old('notes', $equipment?->notes) }}</textarea>
            @error('notes')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
