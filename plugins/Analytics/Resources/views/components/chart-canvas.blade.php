@props([
    'id',
    'height' => 'h-56',
])

<div {{ $attributes->class(['relative w-full', $height]) }}>
    <canvas id="{{ $id }}"></canvas>
</div>
