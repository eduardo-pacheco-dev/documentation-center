<div class="flex flex-wrap items-center justify-between gap-3">
    <form
        method="GET"
        action="{{ route('admin.form-templates.index') }}"
        class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
    >
        @foreach (['search', 'view', 'sort', 'direction', 'type'] as $param)
            @if (request($param) !== null && request($param) !== '')
                <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
            @endif
        @endforeach

        <label for="per-page" class="sr-only">Itens por página</label>
        <select
            id="per-page"
            name="per_page"
            onchange="this.form.submit()"
            class="rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 py-1.5 pl-2 pr-8 text-sm text-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
        >
            @foreach ([12, 24, 36] as $option)
                <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} por página</option>
            @endforeach
        </select>
    </form>

    <x-pagination :paginator="$templates" />
</div>
