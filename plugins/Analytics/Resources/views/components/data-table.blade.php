@props(['columns' => []])

<div class="w-full overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
        <thead class="bg-gray-50/70 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400 dark:bg-white/[0.03] dark:text-gray-500">
            <tr>
                @foreach ($columns as $column)
                    <th class="px-5 py-3 font-semibold">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
            {{ $slot }}
        </tbody>
    </table>
</div>
