<x-layouts.admin title="Nova equipe">
    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
        <a href="{{ route('admin.teams.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Equipes</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <span class="truncate text-gray-400 dark:text-gray-500">Nova equipe</span>
    </nav>

    <x-ui.page-header category="Acompanhamento" title="Nova equipe" description="Cadastre a equipe, líder e status inicial." icon="users" class="mt-4">
        <x-ui.action :href="route('admin.teams.index')" icon="chevron-left" variant="ghost">Voltar</x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <x-team-tracking::partials.team-form :action="route('admin.teams.store')" :users="$users" :statuses="$statuses" />
    </div>
</x-layouts.admin>
