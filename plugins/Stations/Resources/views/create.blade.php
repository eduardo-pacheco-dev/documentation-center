<x-layouts.admin title="Nova estação">
    <x-ui.page-header
        category="Inventário"
        title="Nova estação"
        description="Registre um novo elemento de rede com a infraestrutura e o endereço associados."
        icon="signal"
    >
        <x-ui.action :href="route('admin.stations.index')" variant="secondary" icon="chevron-left">
            Voltar
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <x-stations::partials.form
            :action="route('admin.stations.store')"
            submit-label="Cadastrar estação"
        />
    </div>
</x-layouts.admin>
