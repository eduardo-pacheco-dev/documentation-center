<x-layouts.admin :title="$catalogItem->name">
    @php
        $price = (float) $catalogItem->price;
        $cost = (float) $catalogItem->cost;
        $margin = $price > 0 ? (($price - $cost) / $price) * 100 : null;
        $money = fn (float $value): string => 'R$ '.number_format($value, 2, ',', '.');
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon :name="$catalogItem->type->value === 'product' ? 'cube' : 'wrench-screwdriver'" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                {{ $catalogItem->name }}
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <x-catalog-item-type :catalog-item="$catalogItem" />
                <x-catalog-item-status :catalog-item="$catalogItem" />
                @if ($catalogItem->code)
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $catalogItem->code }}</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.catalog.edit', $catalogItem) }}"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </a>

            <a
                href="{{ route('admin.catalog.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Voltar
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Preço e custo</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Preço de venda</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $money($price) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Custo</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $money($cost) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Margem</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $margin !== null ? number_format($margin, 1, ',', '.').'%' : '—' }}
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Estoque e unidade</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Unidade</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $catalogItem->unit ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Estoque</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">
                        @if ($catalogItem->type->value === 'product')
                            {{ $catalogItem->stock_quantity !== null ? rtrim(rtrim(number_format((float) $catalogItem->stock_quantity, 2, ',', '.'), '0'), ',') : '—' }}
                        @else
                            Não se aplica
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Descrição</h2>
            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                {{ $catalogItem->description ?? 'Nenhuma descrição registrada.' }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:col-span-2 lg:col-span-3 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Observações</h2>
            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                {{ $catalogItem->notes ?? 'Nenhuma observação registrada.' }}
            </p>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        Criado em {{ $catalogItem->created_at->format('d/m/Y H:i') }}
        &middot;
        Atualizado em {{ $catalogItem->updated_at->format('d/m/Y H:i') }}
    </div>
</x-layouts.admin>
