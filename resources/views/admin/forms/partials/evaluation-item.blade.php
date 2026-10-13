<div class="flex items-start gap-3 px-5 py-4" data-evaluation-item>
    <img
        src="https://ui-avatars.com/api/?name={{ urlencode($evaluation->user?->name ?? 'Sistema') }}&background=0D8ABC&color=fff&size=32"
        alt="{{ $evaluation->user?->name ?? 'Sistema' }}"
        class="h-8 w-8 shrink-0 rounded-full"
    >
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <x-form-status :status="$evaluation->status" />
            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $evaluation->user?->name ?? '—' }}</span>
            <x-relative-time :value="$evaluation->created_at" class="text-xs text-gray-500 dark:text-gray-400" />
        </div>
        @if ($evaluation->notes)
            <p class="mt-2 whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300">{{ $evaluation->notes }}</p>
        @endif
    </div>
</div>
