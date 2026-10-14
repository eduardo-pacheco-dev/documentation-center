@props([
    'name',
    'id' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Digite para buscar...',
    'emptyMessage' => 'Nenhum resultado encontrado.',
    'required' => false,
])

@php
    $id = $id ?? $name;

    $normalizedOptions = collect($options)
        ->map(fn ($option) => [
            'value' => (string) ($option['value'] ?? ''),
            'label' => (string) ($option['label'] ?? ''),
        ])
        ->values()
        ->all();

    $selectedValue = (string) ($selected ?? '');
@endphp

<div
    x-data="{
        open: false,
        search: '',
        value: @js($selectedValue),
        options: @js($normalizedOptions),
        active: 0,
        init() {
            this.search = this.selectedLabel;
        },
        get selected() {
            return this.options.find((option) => option.value === this.value) ?? null;
        },
        get selectedLabel() {
            return this.selected ? this.selected.label : '';
        },
        get filtered() {
            const term = this.search.trim().toLowerCase();

            if (term === '' || this.search === this.selectedLabel) {
                return this.options;
            }

            return this.options.filter((option) => option.label.toLowerCase().includes(term));
        },
        openList() {
            this.open = true;
            this.active = 0;
        },
        closeList() {
            this.open = false;
            this.search = this.selectedLabel;
        },
        choose(option) {
            this.value = option.value;
            this.search = option.label;
            this.open = false;
        },
        clear() {
            this.value = '';
            this.search = '';
            this.open = true;
            this.$nextTick(() => this.$refs.input.focus());
        },
        move(step) {
            if (! this.open) {
                this.open = true;
                return;
            }

            const max = this.filtered.length - 1;
            this.active = Math.min(Math.max(this.active + step, 0), Math.max(max, 0));
        },
        chooseActive() {
            const option = this.filtered[this.active];
            if (option) {
                this.choose(option);
            }
        },
    }"
    @click.away="closeList()"
    @keydown.escape.stop="closeList()"
    {{ $attributes->merge(['class' => 'relative']) }}
>
    <input type="hidden" name="{{ $name }}" :value="value">

    <div class="relative">
        <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />

        <input
            type="text"
            x-ref="input"
            id="{{ $id }}"
            x-model="search"
            @focus="openList()"
            @input="open = true; active = 0"
            @keydown.arrow-down.prevent="move(1)"
            @keydown.arrow-up.prevent="move(-1)"
            @keydown.enter.prevent="chooseActive()"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            :aria-expanded="open"
            aria-controls="{{ $id }}-listbox"
            @if ($required) required @endif
            class="block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-2 pl-9 pr-9 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >

        <button
            type="button"
            x-show="value !== ''"
            x-cloak
            @click="clear()"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-full p-0.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            aria-label="Limpar seleção"
        >
            <x-icon name="x-mark" class="h-3.5 w-3.5" />
        </button>

        <x-icon
            x-show="value === ''"
            name="chevron-up-down"
            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500"
        />
    </div>

    <div
        id="{{ $id }}-listbox"
        x-show="open"
        x-cloak
        role="listbox"
        class="absolute z-50 mt-1 max-h-56 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-900"
    >
        <template x-for="(option, index) in filtered" :key="option.value">
            <button
                type="button"
                role="option"
                @click="choose(option)"
                @mouseenter="active = index"
                :aria-selected="option.value === value"
                :class="active === index ? 'bg-indigo-50 dark:bg-indigo-500/10' : ''"
                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800"
            >
                <span class="min-w-0 flex-1 truncate" x-text="option.label"></span>
                <x-icon x-show="option.value === value" name="check" class="h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" />
            </button>
        </template>

        <p x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $emptyMessage }}</p>
    </div>
</div>
