@props([
    'id',
    'height' => 'h-72',
])

<div id="{{ $id }}" {{ $attributes->class(['w-full', $height]) }}></div>
