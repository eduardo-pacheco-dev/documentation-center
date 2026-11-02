<x-layouts.admin title="Nova station">
    <x-ui.page-header
        category="Stations"
        title="Nova station"
        description="Cadastre uma nova station com identificação, localização e contatos."
        icon="signal"
    >
        <x-ui.action :href="route('stations.index')" variant="secondary" icon="arrow-uturn-left">
            Voltar
        </x-ui.action>
    </x-ui.page-header>

    <form method="POST" action="{{ route('stations.store') }}" class="mt-7 max-w-4xl">
        @csrf

        @include('stations::partials.form')

        <div class="mt-5 flex flex-wrap justify-end gap-2">
            <x-ui.action :href="route('stations.index')" variant="secondary">
                Cancelar
            </x-ui.action>

            <x-ui.action type="submit" icon="check">
                Salvar station
            </x-ui.action>
        </div>
    </form>
</x-layouts.admin>
