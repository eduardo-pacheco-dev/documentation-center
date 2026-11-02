<x-layouts.admin title="Editar station">
    <x-ui.page-header
        category="Stations"
        title="Editar {{ $station->code }}"
        description="Atualize os dados da station {{ $station->name }}."
        icon="signal"
    >
        <x-ui.action :href="route('stations.show', $station)" variant="secondary" icon="arrow-uturn-left">
            Voltar
        </x-ui.action>
    </x-ui.page-header>

    <form method="POST" action="{{ route('stations.update', $station) }}" class="mt-7 max-w-4xl">
        @csrf
        @method('PUT')

        @include('stations::partials.form')

        <div class="mt-5 flex flex-wrap justify-end gap-2">
            <x-ui.action :href="route('stations.show', $station)" variant="secondary">
                Cancelar
            </x-ui.action>

            <x-ui.action type="submit" icon="check">
                Salvar alterações
            </x-ui.action>
        </div>
    </form>
</x-layouts.admin>
