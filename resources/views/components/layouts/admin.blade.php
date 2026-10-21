@props(['title' => null])

<x-layouts.dashboard :title="$title ?? config('app.name')">
    {{ $slot }}
</x-layouts.dashboard>
