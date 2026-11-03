<x-layouts.admin title="Editar {{ $followUp->title }}">
    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
        <a href="{{ route('admin.follow-ups.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Follow-ups</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <a href="{{ route('admin.follow-ups.show', $followUp) }}" class="truncate font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">{{ $followUp->title }}</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <span class="truncate text-gray-400 dark:text-gray-500">Editar</span>
    </nav>

    <x-ui.page-header
        category="Acompanhamento"
        title="Editar follow-up"
        description="Atualize os dados e o andamento do acompanhamento."
        icon="clipboard-document-list"
        class="mt-4"
    >
        <x-ui.action :href="route('admin.follow-ups.show', $followUp)" icon="eye" variant="secondary">
            Ver follow-up
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <x-follow-up::partials.form
            :action="route('admin.follow-ups.update', $followUp)"
            method="PUT"
            :follow-up="$followUp"
            :users="$users"
            :statuses="$statuses"
            :priorities="$priorities"
            submit-label="Salvar alterações"
        />
    </div>
</x-layouts.admin>
