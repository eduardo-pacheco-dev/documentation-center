<x-layouts.admin title="Editar site {{ $site->site_id }}">
    <x-ui.page-header
        category="Nokia"
        title="Editar site"
        description="Atualize os dados do site e o andamento do checklist."
        icon="signal"
    >
        <x-ui.action :href="route('admin.nokia.sites.index')" variant="secondary" icon="chevron-left">
            Voltar
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <x-nokia::partials.form
            :action="route('admin.nokia.sites.update', $site)"
            method="PUT"
            :site="$site"
            submit-label="Salvar alterações"
        />
    </div>
</x-layouts.admin>
