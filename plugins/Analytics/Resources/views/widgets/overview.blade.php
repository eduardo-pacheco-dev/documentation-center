@php
    $statusTone = function (?string $status): string {
        $value = mb_strtolower((string) $status);

        return match (true) {
            str_contains($value, 'aceit'), str_contains($value, 'conclu'), str_contains($value, 'entreg'), str_contains($value, 'finaliz') => 'bg-emerald-500',
            str_contains($value, 'aguardando'), str_contains($value, 'pend'), str_contains($value, 'impeditiv'), str_contains($value, 'corre') => 'bg-amber-500',
            str_contains($value, 'cancel') => 'bg-red-500',
            default => 'bg-indigo-500',
        };
    };

    $maxStatus = max(1, (int) $statuses->max('total'));
@endphp

<section class="flex h-full flex-col rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
        <div class="flex min-w-0 items-center gap-3">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="chart-bar" class="h-4 w-4" />
            </span>

            <div class="min-w-0">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Status das OS</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $total }} OS monitoradas</p>
            </div>
        </div>

        <x-ui.badge tone="indigo">{{ $total }}</x-ui.badge>
    </div>

    <div class="grid grid-cols-2 gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
        <a href="{{ route('admin.analytics.index') }}" class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3 transition hover:border-emerald-200 dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Aceitas</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-700 dark:text-emerald-300">{{ $accepted }}</p>
        </a>

        <a href="{{ route('admin.analytics.index') }}" class="rounded-xl border border-amber-100 bg-amber-50/60 p-3 transition hover:border-amber-200 dark:border-amber-500/20 dark:bg-amber-500/10">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Pendentes</p>
            <p class="mt-1 text-2xl font-semibold text-amber-700 dark:text-amber-300">{{ $pending }}</p>
        </a>
    </div>

    <div class="flex-1 px-5 py-4">
        @forelse ($statuses as $status)
            <div class="mb-3 last:mb-0">
                <div class="flex items-center justify-between gap-2 text-xs">
                    <span class="truncate font-medium text-gray-700 dark:text-gray-200">{{ $status->status }}</span>
                    <span class="shrink-0 font-mono text-gray-400 dark:text-gray-500">{{ $status->total }}</span>
                </div>

                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                    <div class="h-full rounded-full {{ $statusTone($status->status) }}" style="width: {{ max(4, ((int) $status->total / $maxStatus) * 100) }}%"></div>
                </div>
            </div>
        @empty
            <p class="py-6 text-center text-xs text-gray-400 dark:text-gray-500">Nenhuma OS importada ainda.</p>
        @endforelse
    </div>

    <div class="border-t border-gray-100 px-5 py-3 dark:border-white/10">
        <a href="{{ route('admin.analytics.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
            Ver analytics
            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
        </a>
    </div>
</section>
