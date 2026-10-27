<div {{ $attributes->class(['rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-900']) }}>
    <x-icon name="clipboard-document-list" class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
        {{ $slot }}
    </p>
</div>
