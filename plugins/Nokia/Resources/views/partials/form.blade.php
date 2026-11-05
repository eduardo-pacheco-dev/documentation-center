@props([
    'action',
    'method' => 'POST',
    'site' => null,
    'submitLabel' => 'Salvar',
    'prefix' => '',
])

<form method="POST" action="{{ $action }}" class="space-y-8">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-nokia::partials.form-fields :prefix="$prefix" :site="$site" />

    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5 dark:border-white/10">
        <x-ui.action type="submit" icon="check">{{ $submitLabel }}</x-ui.action>
        <x-ui.action :href="route('admin.nokia.sites.index')" variant="secondary">Cancelar</x-ui.action>
    </div>
</form>
