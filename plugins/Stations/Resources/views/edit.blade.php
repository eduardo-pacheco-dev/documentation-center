<x-layouts.admin title="Editar {{ $station->station_id ?: $station->site_id }}">
    <x-ui.page-header
        category="Inventário"
        title="Editar estação"
        description="Atualize os dados de {{ $station->station_id ?: $station->site_id }}."
        icon="signal"
    >
        <x-ui.action :href="route('admin.stations.show', $station)" variant="secondary" icon="eye">
            Ver estação
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <x-stations::partials.form
            :action="route('admin.stations.update', $station)"
            method="PUT"
            :station="$station"
            submit-label="Salvar alterações"
        />
    </div>
</x-layouts.admin>
