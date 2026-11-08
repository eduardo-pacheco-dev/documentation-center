@props([
    'counts',
    'view',
    'lists',
    'activeList' => null,
    'listCounts',
    'listPayloadFor',
])

<aside class="space-y-4 lg:sticky lg:top-6 lg:self-start">
    <x-tasks::partials.views-card :counts="$counts" :view="$view" :active-list="$activeList" />
    <x-tasks::partials.lists-card :lists="$lists" :active-list="$activeList" :list-counts="$listCounts" :list-payload-for="$listPayloadFor" />
</aside>
