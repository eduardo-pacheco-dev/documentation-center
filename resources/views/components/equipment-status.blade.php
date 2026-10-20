@props(['equipment'])

@switch ($equipment->status->value)
    @case('active')
        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-500/15 dark:text-green-300">
            <x-icon name="check-circle" class="h-3.5 w-3.5" />
            Ativo
        </span>
    @break

    @case('maintenance')
        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
            <x-icon name="exclamation-triangle" class="h-3.5 w-3.5" />
            Em manutenção
        </span>
    @break

    @default
        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
            <x-icon name="x-circle" class="h-3.5 w-3.5" />
            Inativo
        </span>
    @endswitch
