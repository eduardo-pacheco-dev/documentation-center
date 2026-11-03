<x-layouts.admin title="Novo follow-up">
    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
        <a href="{{ route('admin.follow-ups.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Follow-ups</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <span class="truncate text-gray-400 dark:text-gray-500">Novo follow-up</span>
    </nav>

    <x-ui.page-header
        category="Acompanhamento"
        title="Novo follow-up"
        description="Crie um acompanhamento com responsável, prazo e prioridade."
        icon="clipboard-document-list"
        class="mt-4"
    >
        <x-ui.action :href="route('admin.follow-ups.index')" icon="chevron-left" variant="ghost">
            Voltar
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <x-follow-up::partials.form
            :action="route('admin.follow-ups.store')"
            :users="$users"
            :statuses="$statuses"
            :priorities="$priorities"
        />
    </div>
</x-layouts.admin>
