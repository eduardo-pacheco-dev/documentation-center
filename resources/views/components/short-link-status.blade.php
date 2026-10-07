@props(['shortLink'])

@if ($shortLink->is_active === false)
    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
        <x-icon name="x-circle" class="h-3.5 w-3.5" />
        Desativado
    </span>
@elseif ($shortLink->isExpired())
    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-500/15 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-300">
        <x-icon name="clock" class="h-3.5 w-3.5" />
        Expirado
    </span>
@elseif ($shortLink->hasReachedAccessLimit())
    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-500/15 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-300">
        <x-icon name="exclamation-triangle" class="h-3.5 w-3.5" />
        Limite atingido
    </span>
@else
    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-500/15 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">
        <x-icon name="check-circle" class="h-3.5 w-3.5" />
        Ativo
    </span>
@endif