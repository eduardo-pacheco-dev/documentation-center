@props(['paginator'])

<nav role="navigation" aria-label="Paginação" class="flex flex-wrap items-center gap-3">
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Exibindo {{ $paginator->total() > 0 ? $paginator->firstItem() : 0 }} a {{ $paginator->total() > 0 ? $paginator->lastItem() : 0 }} de {{ $paginator->total() }} registros
    </p>

    <div class="flex items-center gap-1 whitespace-nowrap">
        @foreach ($paginator->linkCollection() as $link)
            @if ($link['label'] === '...')
                <span class="px-1 text-sm text-gray-400 dark:text-gray-500">{{ $link['label'] }}</span>
            @elseif ($link['url'] === null)
                <span
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-gray-300 dark:text-gray-600"
                    aria-disabled="true"
                    aria-label="{{ __($loop->first ? 'pagination.previous' : 'pagination.next') }}"
                >
                    @if ($loop->first)
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>
                    @else
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>
                    @endif
                </span>
            @elseif ($link['active'])
                <span
                    aria-current="page"
                    class="inline-flex h-8 min-w-8 items-center justify-center rounded-md bg-gray-900 px-2 text-sm font-medium text-white dark:bg-white dark:text-gray-900"
                >{{ $link['label'] }}</span>
            @elseif ($loop->first || $loop->last)
                <a
                    href="{{ $link['url'] }}"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    aria-label="{{ __($loop->first ? 'pagination.previous' : 'pagination.next') }}"
                >
                    @if ($loop->first)
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>
                    @else
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>
                    @endif
                </a>
            @else
                <a
                    href="{{ $link['url'] }}"
                    class="inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    aria-label="{{ __('Ir para a página :page', ['page' => $link['page']]) }}"
                >{{ $link['label'] }}</a>
            @endif
        @endforeach
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</p>
</nav>