<x-layouts.admin :title="$workOrder->number">
    @php
        $money = fn (float $value): string => 'R$ '.number_format($value, 2, ',', '.');
        $quantity = fn ($value): string => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                {{ $workOrder->number }}
            </h1>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <x-work-order-status :work-order="$workOrder" />
                <x-work-order-priority :work-order="$workOrder" />
                <a
                    href="{{ route('admin.clients.show', $workOrder->client) }}"
                    class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                >
                    <x-icon name="building-office" class="h-4 w-4" />
                    {{ $workOrder->client->name }}
                </a>
                @if ($workOrder->erb)
                    <a
                        href="{{ route('admin.erbs.show', $workOrder->erb) }}"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                    >
                        <x-icon name="signal" class="h-4 w-4" />
                        {{ $workOrder->erb->code }}
                    </a>
                @endif
                @if ($workOrder->radioLink)
                    <a
                        href="{{ route('admin.radio-links.show', $workOrder->radioLink) }}"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                    >
                        <x-icon name="arrows-right-left" class="h-4 w-4" />
                        {{ $workOrder->radioLink->code }}
                    </a>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $workOrder)
                <button
                    type="button"
                    data-modal-open="work-order-edit-modal"
                    class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                >
                    <x-icon name="pencil-square" class="h-4 w-4" />
                    Editar
                </button>
            @endcan

            <a
                href="{{ route('admin.work-orders.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Voltar
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Datas</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Abertura</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->opened_at->format('d/m/Y') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Previsão de entrega</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->due_at?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Conclusão</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->completed_at?->format('d/m/Y') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Resumo</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Itens</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->items->count() }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Total</dt>
                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $money((float) $workOrder->total) }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:col-span-2 lg:col-span-1">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Descrição</h2>
            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                {{ $workOrder->description ?? 'Nenhuma descrição registrada.' }}
            </p>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <x-icon name="briefcase" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Projeto</h2>
            </div>

            @if ($workOrder->project === null)
                <form method="POST" action="{{ route('admin.work-orders.project.store', $workOrder) }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-3 py-1.5 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
                    >
                        <x-icon name="plus" class="h-4 w-4" />
                        Criar projeto
                    </button>
                </form>
            @endif
        </div>

        @if ($workOrder->project !== null)
            <div class="mt-3">
                <a
                    href="{{ route('admin.projects.show', $workOrder->project) }}"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                >
                    {{ $workOrder->project->name }}
                </a>
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $workOrder->project->status->label() }}</p>
            </div>
        @else
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Nenhum projeto vinculado. Crie um projeto a partir desta OS para iniciar o planejamento.
            </p>
        @endif
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-800">
            <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Itens da OS</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">Descrição</th>
                        <th class="px-5 py-3 font-medium">Unidade</th>
                        <th class="px-5 py-3 text-right font-medium">Quantidade</th>
                        <th class="px-5 py-3 text-right font-medium">Valor unitário</th>
                        <th class="px-5 py-3 text-right font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($workOrder->items as $item)
                        <tr>
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $item->description }}
                                @if ($item->catalogItem)
                                    <span class="mt-0.5 block text-xs text-gray-400 dark:text-gray-500">{{ $item->catalogItem->name }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $item->unit ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-500 dark:text-gray-400">{{ $quantity($item->quantity) }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-500 dark:text-gray-400">{{ $money((float) $item->unit_price) }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-gray-900 dark:text-gray-100">{{ $money((float) $item->total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-6 text-gray-500 dark:text-gray-400" colspan="5">Nenhum item registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <td class="px-5 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-200" colspan="4">Total</td>
                        <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $money((float) $workOrder->total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Observações</h2>
        <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
            {{ $workOrder->notes ?? 'Nenhuma observação registrada.' }}
        </p>
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        Criado em {{ $workOrder->created_at->format('d/m/Y H:i') }}
        &middot;
        Atualizado em {{ $workOrder->updated_at->format('d/m/Y H:i') }}
    </div>

    @can('update', $workOrder)
        @include('admin.work-orders.partials.edit-modal', ['workOrder' => $workOrder])
    @endcan

    <script>
        const openModal = (modal) => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            const error = modal.querySelector('[data-modal-error]');
            if (error) {
                error.scrollIntoView({ block: 'center' });
            }
        };

        const closeModal = (modal) => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        document.addEventListener('click', (event) => {
            const opener = event.target.closest('[data-modal-open]');

            if (opener) {
                event.preventDefault();

                const modal = document.getElementById(opener.dataset.modalOpen);
                if (modal) {
                    openModal(modal);
                }

                return;
            }

            const closer = event.target.closest('[data-modal-close]');
            if (closer) {
                closeModal(closer.closest('[role="dialog"]'));
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(closeModal);
            }
        });

        document.querySelectorAll('[role="dialog"][data-open]').forEach(openModal);

        const wizard = document.querySelector('[data-wizard]');

        if (wizard) {
            const panels = Array.from(wizard.querySelectorAll('[data-wizard-panel]'));
            const stepsList = wizard.querySelector('[data-wizard-steps]');
            const prevButton = wizard.querySelector('[data-wizard-prev]');
            const nextButton = wizard.querySelector('[data-wizard-next]');
            const submitButton = wizard.querySelector('[data-wizard-submit]');
            const scroller = wizard.closest('.overflow-y-auto');
            const baseIndicator = 'inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition';
            const inactiveIndicator = baseIndicator + ' border-gray-300 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800';
            const activeIndicator = baseIndicator + ' border-gray-900 bg-gray-900 text-white dark:border-white dark:bg-white dark:text-gray-900';
            const doneIndicator = baseIndicator + ' border-green-500 text-green-600 dark:text-green-400';
            let current = 0;

            if (stepsList) {
                stepsList.innerHTML = panels.map((panel, index) => `
                    <li>
                        <button type="button" data-wizard-indicator="${index}" class="${inactiveIndicator}">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-200 text-[0.65rem] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">${index + 1}</span>
                            <span>${panel.dataset.wizardTitle ?? ''}</span>
                        </button>
                    </li>
                `).join('');
            }

            const indicators = stepsList ? Array.from(stepsList.querySelectorAll('[data-wizard-indicator]')) : [];

            const firstInvalid = (panel) => Array.from(panel.querySelectorAll('input, select, textarea'))
                .find((field) => !field.checkValidity());

            const render = () => {
                panels.forEach((panel, index) => panel.classList.toggle('hidden', index !== current));

                indicators.forEach((indicator, index) => {
                    indicator.className = index === current
                        ? activeIndicator
                        : (index < current ? doneIndicator : inactiveIndicator);
                });

                prevButton.classList.toggle('hidden', current === 0);
                nextButton.classList.toggle('hidden', current === panels.length - 1);
                submitButton.classList.toggle('hidden', current !== panels.length - 1);
                submitButton.classList.toggle('inline-flex', current === panels.length - 1);
            };

            const goTo = (index) => {
                current = Math.max(0, Math.min(index, panels.length - 1));
                render();

                if (scroller) {
                    scroller.scrollTop = 0;
                }
            };

            nextButton.addEventListener('click', () => {
                const invalid = firstInvalid(panels[current]);

                if (invalid) {
                    invalid.reportValidity();
                    return;
                }

                goTo(current + 1);
            });

            prevButton.addEventListener('click', () => goTo(current - 1));

            indicators.forEach((indicator) => {
                indicator.addEventListener('click', () => goTo(Number(indicator.dataset.wizardIndicator)));
            });

            wizard.addEventListener('submit', (event) => {
                for (let index = 0; index < panels.length; index++) {
                    const invalid = firstInvalid(panels[index]);

                    if (invalid) {
                        event.preventDefault();
                        goTo(index);
                        invalid.reportValidity();
                        return;
                    }
                }
            });

            document.querySelectorAll('[data-modal-open="work-order-edit-modal"]').forEach((opener) => {
                opener.addEventListener('click', () => goTo(0));
            });

            const errorPanel = panels.findIndex((panel) => panel.querySelector('.text-red-600'));

            goTo(errorPanel > -1 ? errorPanel : 0);
        }
    </script>
</x-layouts.admin>
