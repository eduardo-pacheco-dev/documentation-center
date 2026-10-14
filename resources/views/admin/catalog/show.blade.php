<x-layouts.admin :title="$catalogItem->name">
    @php
        $price = (float) $catalogItem->price;
        $cost = (float) $catalogItem->cost;
        $margin = $price > 0 ? (($price - $cost) / $price) * 100 : null;
        $isProduct = $catalogItem->type->value === 'product';
        $money = fn (float $value): string => 'R$ '.number_format($value, 2, ',', '.');
        $quantity = function ($value): string {
            if ($value === null) {
                return '—';
            }

            return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
        };
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                Catálogo
            </p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                <x-icon :name="$isProduct ? 'cube' : 'wrench-screwdriver'" class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                {{ $catalogItem->name }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $catalogItem->code ?? $catalogItem->type->label() }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-catalog-item-type :catalog-item="$catalogItem" />
                <x-catalog-item-status :catalog-item="$catalogItem" />
                @if ($catalogItem->unit)
                    <span class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        por {{ $catalogItem->unit }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.catalog.index') }}"
                class="inline-flex items-center gap-1.5 rounded-2xl border border-gray-200/70 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-500/40"
            >
                <x-icon name="chevron-left" class="h-4 w-4" />
                Voltar
            </a>

            <a
                href="{{ route('admin.catalog.edit', $catalogItem) }}"
                class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="chart-bar" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $money($price) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Preço de venda</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-violet-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <x-icon name="rectangle-stack" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $money($cost) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Custo</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-emerald-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-icon name="arrow-path" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                {{ $margin !== null ? number_format($margin, 1, ',', '.').'%' : '—' }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Margem</p>
        </div>
        <div class="group relative overflow-hidden rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-600/5 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-amber-500/40">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <x-icon name="cube" class="h-5 w-5" />
            </span>
            <p class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                {{ $isProduct ? $quantity($catalogItem->stock_quantity) : '—' }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Estoque</p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="clipboard-document-list" class="h-5 w-5" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Informações</h2>
            </div>

            <dl class="mt-4 text-sm">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Tipo</dt>
                    <dd><x-catalog-item-type :catalog-item="$catalogItem" /></dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Código</dt>
                    <dd class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $catalogItem->code ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Unidade</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $catalogItem->unit ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-2.5 dark:border-gray-800">
                    <dt class="text-gray-500 dark:text-gray-400">Estoque</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $isProduct ? $quantity($catalogItem->stock_quantity) : 'Não se aplica' }}
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-2.5 pb-0">
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                    <dd><x-catalog-item-status :catalog-item="$catalogItem" /></dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md sm:col-span-2 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                    <x-icon name="document-text" class="h-5 w-5" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Descrição</h2>
            </div>

            <p class="mt-4 text-sm text-gray-700 dark:text-gray-300">
                {{ $catalogItem->description ?? 'Nenhuma descrição registrada.' }}
            </p>
        </section>

        <section class="rounded-2xl border border-gray-200/70 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md sm:col-span-2 lg:col-span-3 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-500/40">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                    <x-icon name="clipboard-document" class="h-5 w-5" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Observações</h2>
            </div>

            <p class="mt-4 text-sm text-gray-700 dark:text-gray-300">
                {{ $catalogItem->notes ?? 'Nenhuma observação registrada.' }}
            </p>
        </section>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200/70 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        <x-icon name="clock" class="h-3.5 w-3.5" />
        <span>Criado em {{ $catalogItem->created_at->format('d/m/Y H:i') }}</span>
        <span class="text-gray-300 dark:text-gray-600">&middot;</span>
        <span>Atualizado em {{ $catalogItem->updated_at->format('d/m/Y H:i') }}</span>
    </div>
</x-layouts.admin>
