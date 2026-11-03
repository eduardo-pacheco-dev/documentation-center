<section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400">Plugin</p>
            <h2 class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $widget->title() }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
        </div>

        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
            <x-icon name="cube" class="h-5 w-5" />
        </span>
    </div>
</section>
