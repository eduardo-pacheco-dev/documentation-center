@props([
    'title',
    'lines' => [],
])

<div {{ $attributes->class('rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300') }}>
    <p class="font-semibold">{{ $title }}</p>

    @if ($lines !== [])
        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach ($lines as $line)
                <li>{{ $line }}</li>
            @endforeach
        </ul>
    @endif
</div>
