@props(['tag' => 'section'])

<{{ $tag }} {{ $attributes->class(['min-w-0 overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04)] dark:border-white/10 dark:bg-[#171716]']) }}>
    {{ $slot }}
</{{ $tag }}>
