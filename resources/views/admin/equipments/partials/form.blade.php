@php
    $equipment = $equipment ?? null;
    $statuses = $statuses ?? \App\Enums\EquipmentStatus::cases();
    $wizard = $wizard ?? false;
    $fieldSuffix = $equipment?->getKey() ? '-'.$equipment->getKey() : '-novo';

    $inputClass = 'mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $errorClass = 'mt-1 text-xs text-red-600 dark:text-red-400';

    $panelClass = fn (int $index): string => trim('grid gap-5 sm:grid-cols-2 '.($wizard && $index > 0 ? 'hidden' : ''));
@endphp

<div class="space-y-5">
    <div
        @if ($wizard) data-wizard-panel data-wizard-title="Identificação" @endif
        class="{{ $panelClass(0) }}"
    >
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
            <p class="{{ $labelClass }}">Código</p>

            @if ($equipment?->code)
                <div class="mt-1 inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                    <x-icon name="check-circle" class="h-4 w-4" />
                    <span class="font-mono">{{ $equipment->code }}</span>
                </div>
            @else
                <div class="mt-1 flex items-center gap-2 rounded-xl border border-dashed border-indigo-300 bg-indigo-50/60 px-3 py-2 text-sm font-medium text-indigo-700 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-300">
                    <x-icon name="arrow-path" class="h-4 w-4" />
                    Gerado automaticamente
                </div>
            @endif

            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sequencial por usuário, ex.: EQP-0001.</p>
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
    </div>

    <div
        @if ($wizard) data-wizard-panel data-wizard-title="Detalhes" @endif
        class="{{ $panelClass(1) }}"
    >
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

    @if ($wizard)
        <div data-wizard-panel data-wizard-title="Revisão" class="{{ $panelClass(2) }}">
            <div class="overflow-hidden rounded-2xl border border-gray-200/70 bg-gray-50/60 sm:col-span-2 dark:border-gray-800 dark:bg-gray-800/30">
                <div class="flex items-start gap-3 border-b border-gray-200/70 px-5 py-4 dark:border-gray-800">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon name="clipboard-document" class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Confira as informações</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Você poderá editar esses dados depois, se precisar.</p>
                    </div>
                </div>

                <dl class="grid gap-x-6 gap-y-4 px-5 py-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Código</dt>
                        <dd class="mt-1 inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-2 py-1 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                            <x-icon name="arrow-path" class="h-3.5 w-3.5" />
                            Gerado automaticamente
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Nome</dt>
                        <dd data-review-field="name" class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">—</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Categoria</dt>
                        <dd data-review-field="category" class="mt-1 text-sm text-gray-700 dark:text-gray-300">—</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</dt>
                        <dd data-review-field="status" class="mt-1 text-sm text-gray-700 dark:text-gray-300">—</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Fabricante</dt>
                        <dd data-review-field="manufacturer" class="mt-1 text-sm text-gray-700 dark:text-gray-300">—</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Modelo</dt>
                        <dd data-review-field="model" class="mt-1 text-sm text-gray-700 dark:text-gray-300">—</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Descrição</dt>
                        <dd data-review-field="description" class="mt-1 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">—</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Observações</dt>
                        <dd data-review-field="notes" class="mt-1 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">—</dd>
                    </div>
                </dl>
            </div>
        </div>
    @endif
</div>
