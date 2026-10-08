@props(['project'])

@switch ($project->status->value)
    @case('active')
        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-500/15 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">
            <x-icon name="check-circle" class="h-3.5 w-3.5" />
            Ativo
        </span>
    @break

    @case('completed')
        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 dark:bg-indigo-500/15 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">
            <x-icon name="flag" class="h-3.5 w-3.5" />
            Concluído
        </span>
    @break

    @default
        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
            <x-icon name="clock" class="h-3.5 w-3.5" />
            Arquivado
        </span>
    @endswitch
