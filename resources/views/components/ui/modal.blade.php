@props([
    'id',
    'title' => null,
    'description' => null,
    'maxWidth' => 'max-w-lg',
    'bodyClass' => 'mt-4',
])

<div
    id="{{ $id }}"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    {{ $attributes->class(['fixed inset-0 z-[100] hidden']) }}
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full {{ $maxWidth }} -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-5 dark:bg-[#1c1c1b]">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                @if (isset($header))
                    {{ $header }}
                @else
                    <h2 id="{{ $id }}-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h2>

                    @if ($description)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                    @endif
                @endif
            </div>

            <button
                type="button"
                data-modal-close
                class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
                aria-label="Fechar"
            >
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>

        <div class="{{ $bodyClass }}">
            {{ $slot }}
        </div>
    </div>
</div>
