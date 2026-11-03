@props([
    'action',
    'method' => 'POST',
    'document' => null,
    'categories',
    'submitLabel',
])

@php
    $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
    $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="{{ $labelClass }}">Título</label>
            <input
                id="title"
                name="title"
                type="text"
                value="{{ old('title', $document?->title) }}"
                required
                maxlength="180"
                class="{{ $inputClass }}"
            >
            @error('title')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category" class="{{ $labelClass }}">Categoria</label>
            <select id="category" name="category" required class="{{ $inputClass }}">
                <option value="">Selecione uma categoria</option>
                @foreach ($categories as $option)
                    <option value="{{ $option->value }}" @selected(old('category', $document?->category?->value) === $option->value)>
                        {{ $option->label() }}
                    </option>
                @endforeach
            </select>
            @error('category')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-end pb-1">
            <label class="flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                <input
                    type="checkbox"
                    name="is_published"
                    value="1"
                    @checked(old('is_published', $document?->is_published ?? true))
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500/30 dark:border-white/20 dark:bg-white/[0.03] dark:text-indigo-400"
                >
                Publicado
            </label>
        </div>

        <div class="sm:col-span-2">
            <label for="summary" class="{{ $labelClass }}">Resumo</label>
            <textarea
                id="summary"
                name="summary"
                rows="2"
                maxlength="300"
                placeholder="Uma frase curta para aparecer na listagem."
                class="{{ $inputClass }} h-auto py-2"
            >{{ old('summary', $document?->summary) }}</textarea>
            @error('summary')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <label for="content" class="{{ $labelClass }}">Conteúdo em Markdown</label>
                <span class="text-xs text-gray-400 dark:text-gray-500">Aceita títulos, listas, links, tabelas e blocos de código.</span>
            </div>
            <textarea
                id="content"
                name="content"
                rows="20"
                required
                spellcheck="false"
                class="{{ $inputClass }} h-auto py-3 font-mono text-[13px] leading-6"
            >{{ old('content', $document?->content) }}</textarea>
            @error('content')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
            <x-icon name="check" class="h-4 w-4" />
            {{ $submitLabel }}
        </button>

        <a href="{{ route('admin.dev-docs.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200/70 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40">
            Cancelar
        </a>
    </div>
</form>
