@props([
    'id',
    'name',
    'files' => [],
    'fileNames' => [],
    'describedBy' => null,
    'invalid' => false,
])

@php
    $fileName = fn ($file, $index) => $fileNames[$index] ?? basename(parse_url((string) $file, PHP_URL_PATH) ?: (string) $file);
    $describedBy = filled($describedBy) ? $describedBy : null;
@endphp

<div {{ $attributes }}>
    @if (! empty($files))
        <ul class="mb-3 space-y-2">
            @foreach ($files as $index => $file)
                <li>
                    <x-public.forms.file-link :href="$file" :name="$fileName($file, $index)" />
                </li>
            @endforeach
        </ul>
    @endif

    <input
        id="{{ $id }}"
        type="file"
        name="{{ $name }}"
        multiple
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($invalid) aria-invalid="true" @endif
        class="block min-h-11 w-full cursor-pointer rounded-xl border border-gray-300 bg-white text-base text-gray-600 transition file:mr-3 file:rounded-lg file:border-0 file:bg-gray-900 file:px-3.5 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 sm:text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:file:bg-white dark:file:text-gray-900 dark:hover:file:bg-gray-200"
    >

    <x-ui.field-help class="mt-2">Até 5 arquivos e 30 MB por arquivo.</x-ui.field-help>
</div>
