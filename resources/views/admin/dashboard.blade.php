<x-layouts.dashboard title="Dashboard">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400 dark:text-gray-500">Visão geral</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ $greeting }}</h1>
            <p class="mt-1.5 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                Bem-vindo ao sistema base. Gerencie sua conta, seus módulos e os plugins instalados.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 text-xs font-medium text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                <x-icon name="home" class="h-4 w-4" />
                {{ now()->locale('pt_BR')->translatedFormat('d \d\e M, Y') }}
            </span>

            @if ($editing)
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500"
                >
                    <x-icon name="check" class="h-4 w-4" />
                    Concluir edição
                </a>
            @else
                <a
                    href="{{ route('admin.dashboard', ['edit' => 1]) }}"
                    class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300"
                >
                    <x-icon name="pencil-square" class="h-4 w-4" />
                    Editar widgets
                </a>
            @endif
        </div>
    </div>

    @if ($editing)
        <div class="mt-4 flex items-start gap-3 rounded-2xl border border-dashed border-indigo-300 bg-indigo-50/60 p-4 dark:border-indigo-500/40 dark:bg-indigo-500/10">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                <x-icon name="pencil-square" class="h-4 w-4" />
            </span>

            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Modo edição</p>
                <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">
                    Remova widgets do painel ou adicione de volta os que estão ocultos.
                </p>
            </div>
        </div>
    @endif

    @if ($widgets->isEmpty())
        <div class="mt-6 rounded-3xl border border-dashed border-gray-200 bg-white dark:border-white/10 dark:bg-[#171716]">
            <x-ui.empty-state
                icon="squares-2x2"
                title="Nenhum widget visível"
                :description="$editing
                    ? 'Adicione um widget da lista abaixo para voltar a exibir conteúdo.'
                    : 'Use o modo de edição para adicionar widgets ao painel.'"
                tone="indigo"
                shape="square"
            >
                @unless ($editing)
                    <x-ui.action :href="route('admin.dashboard', ['edit' => 1])" size="sm" icon="pencil-square" class="mt-4">
                        Editar widgets
                    </x-ui.action>
                @endunless
            </x-ui.empty-state>
        </div>
    @else
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($widgets as $widget)
                @php
                    $spanClass = match ($widget->columnSpan()) {
                        2 => 'sm:col-span-2',
                        3 => 'sm:col-span-2 xl:col-span-3',
                        default => '',
                    };
                @endphp

                <div @class([$spanClass, 'flex flex-col']) data-widget="{{ $widget->id() }}">
                    @if ($editing)
                        <div class="mb-2 flex items-center justify-between gap-2 rounded-xl border border-dashed border-gray-300 bg-white/80 px-3 py-2 dark:border-white/15 dark:bg-white/[0.03]">
                            <span class="min-w-0 truncate text-xs font-semibold text-gray-600 dark:text-gray-300">{{ $widget->title() }}</span>

                            <form method="POST" action="{{ route('admin.widgets.update', ['widget' => $widget->id()]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="hidden" value="1">

                                <button
                                    type="submit"
                                    class="inline-flex h-7 items-center gap-1 rounded-md px-2 text-[11px] font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                                >
                                    <x-icon name="minus-circle" class="h-3.5 w-3.5" />
                                    Remover
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        @include($widget->view(), ['widget' => $widget] + $widget->data(request()))
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($editing)
        <section class="mt-6 overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Widgets ocultos</h2>
                        <x-ui.badge tone="gray">{{ $hiddenWidgets->count() }}</x-ui.badge>
                    </div>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Adicione um widget oculto de volta ao painel.</p>
                </div>
            </div>

            <div class="p-4">
                @if ($hiddenWidgets->isEmpty())
                    <p class="rounded-xl border border-dashed border-gray-200 px-4 py-3 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                        Todos os widgets disponíveis já estão visíveis.
                    </p>
                @else
                    <ul class="grid gap-2 sm:grid-cols-2">
                        @foreach ($hiddenWidgets as $hiddenWidget)
                            <li class="flex items-center justify-between gap-3 rounded-xl border border-gray-200/80 px-3 py-2.5 dark:border-white/10">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">{{ $hiddenWidget->title() }}</p>
                                    <p class="truncate font-mono text-[11px] text-gray-400 dark:text-gray-500">{{ $hiddenWidget->id() }}</p>
                                </div>

                                <form method="POST" action="{{ route('admin.widgets.update', ['widget' => $hiddenWidget->id()]) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="hidden" value="0">

                                    <button
                                        type="submit"
                                        class="inline-flex h-8 shrink-0 items-center gap-1 rounded-lg border border-gray-200 px-2.5 text-xs font-semibold text-gray-600 transition hover:border-indigo-200 hover:text-indigo-600 dark:border-white/10 dark:text-gray-300 dark:hover:border-indigo-500/40 dark:hover:text-indigo-300"
                                    >
                                        <x-icon name="plus" class="h-3.5 w-3.5" />
                                        Adicionar
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    @endif
</x-layouts.dashboard>
