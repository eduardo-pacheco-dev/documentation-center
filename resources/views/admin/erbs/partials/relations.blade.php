{{-- Projects --}}
<div
    id="erb-projects-modal"
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="erb-projects-modal-title"
    @if ($errors->any() && old('modal') === 'projects') data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 flex max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 p-5 dark:border-gray-800">
            <div>
                <h2 id="erb-projects-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="briefcase" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Projetos da ERB
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Marque os projetos que pertencem a esta ERB. Projetos desmarcados serão excluídos.</p>
            </div>

            <button type="button" class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700" data-modal-close>
                <span class="sr-only">Fechar</span>
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        <form
            method="POST"
            action="{{ route('admin.erbs.relations.projects', $erb) }}"
            class="flex min-h-0 flex-1 flex-col"
            data-confirm="Projetos desmarcados serão excluídos. Deseja salvar os vínculos?"
            data-confirm-title="Confirmar projetos da ERB"
            data-confirm-label="Salvar"
        >
            @csrf
            @method('PUT')
            <input type="hidden" name="modal" value="projects">

            @error('projects')
                <p class="px-5 pt-4 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
            @enderror

            <div class="min-h-0 flex-1 overflow-y-auto p-5">
                @if ($projectOptions->isEmpty())
                    <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum projeto disponível.</p>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($projectOptions as $project)
                            <label class="flex cursor-pointer items-center gap-3 py-2.5">
                                <input
                                    type="checkbox"
                                    name="projects[]"
                                    value="{{ $project->getKey() }}"
                                    @checked((int) $project->erb_id === (int) $erb->getKey())
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
                                >
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $project->name }}</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $project->code }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-100 p-5 dark:border-gray-800">
                <button type="button" class="rounded-2xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800" data-modal-close>
                    Cancelar
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    <x-icon name="check" class="h-4 w-4" />
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Work orders --}}
<div
    id="erb-work-orders-modal"
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="erb-work-orders-modal-title"
    @if ($errors->any() && old('modal') === 'work-orders') data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 flex max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 p-5 dark:border-gray-800">
            <div>
                <h2 id="erb-work-orders-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Ordens de serviço da ERB
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Marque as ordens de serviço desta ERB. Ordens desmarcadas serão excluídas.</p>
            </div>

            <button type="button" class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700" data-modal-close>
                <span class="sr-only">Fechar</span>
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        <form
            method="POST"
            action="{{ route('admin.erbs.relations.work-orders', $erb) }}"
            class="flex min-h-0 flex-1 flex-col"
            data-confirm="Ordens de serviço desmarcadas serão excluídas. Deseja salvar os vínculos?"
            data-confirm-title="Confirmar ordens de serviço da ERB"
            data-confirm-label="Salvar"
        >
            @csrf
            @method('PUT')
            <input type="hidden" name="modal" value="work-orders">

            @error('work_orders')
                <p class="px-5 pt-4 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
            @enderror

            <div class="min-h-0 flex-1 overflow-y-auto p-5">
                @if ($workOrderOptions->isEmpty())
                    <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma ordem de serviço disponível.</p>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($workOrderOptions as $workOrder)
                            <label class="flex cursor-pointer items-center gap-3 py-2.5">
                                <input
                                    type="checkbox"
                                    name="work_orders[]"
                                    value="{{ $workOrder->getKey() }}"
                                    @checked((int) $workOrder->erb_id === (int) $erb->getKey())
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
                                >
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $workOrder->number }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-100 p-5 dark:border-gray-800">
                <button type="button" class="rounded-2xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800" data-modal-close>
                    Cancelar
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    <x-icon name="check" class="h-4 w-4" />
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Radio links --}}
<div
    id="erb-radio-links-modal"
    class="fixed inset-0 z-50 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="erb-radio-links-modal-title"
    @if ($errors->any() && old('modal') === 'radio-links') data-open @endif
>
    <div class="absolute inset-0 bg-gray-900/50" data-modal-close></div>

    <div class="absolute left-1/2 top-1/2 flex max-h-[90vh] w-full max-w-2xl -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-900">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 p-5 dark:border-gray-800">
            <div>
                <h2 id="erb-radio-links-modal-title" class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                    <x-icon name="arrows-right-left" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    Radio links da ERB
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Escolha em qual ponta esta ERB entra. Links marcados como "Não vincular" serão excluídos.</p>
            </div>

            <button type="button" class="rounded-full p-1 text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700" data-modal-close>
                <span class="sr-only">Fechar</span>
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        <form
            method="POST"
            action="{{ route('admin.erbs.relations.radio-links', $erb) }}"
            class="flex min-h-0 flex-1 flex-col"
            data-confirm="Radio links marcados como &quot;Não vincular&quot; serão excluídos. Deseja salvar os vínculos?"
            data-confirm-title="Confirmar radio links da ERB"
            data-confirm-label="Salvar"
        >
            @csrf
            @method('PUT')
            <input type="hidden" name="modal" value="radio-links">

            @error('radio_links')
                <p class="px-5 pt-4 text-xs text-red-600 dark:text-red-400" data-modal-error>{{ $message }}</p>
            @enderror

            <div class="min-h-0 flex-1 overflow-y-auto p-5">
                @if ($radioLinkOptions->isEmpty())
                    <p class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum radio link disponível.</p>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($radioLinkOptions as $radioLink)
                            @php
                                $currentEnd = (int) $radioLink->erb_a_id === (int) $erb->getKey()
                                    ? 'a'
                                    : ((int) $radioLink->erb_b_id === (int) $erb->getKey() ? 'b' : '');
                            @endphp

                            <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $radioLink->code }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $radioLink->erbA?->code ?? 'ERB removida' }}
                                        &harr;
                                        {{ $radioLink->erbB?->code ?? 'ERB removida' }}
                                    </p>
                                </div>

                                <select
                                    name="radio_links[{{ $radioLink->getKey() }}][end]"
                                    class="rounded-xl border border-gray-300 px-3 py-1.5 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                                >
                                    <option value="" @selected($currentEnd === '')>Não vincular</option>
                                    <option value="a" @selected($currentEnd === 'a')>Ponta A</option>
                                    <option value="b" @selected($currentEnd === 'b')>Ponta B</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-100 p-5 dark:border-gray-800">
                <button type="button" class="rounded-2xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800" data-modal-close>
                    Cancelar
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    <x-icon name="check" class="h-4 w-4" />
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
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
    })();
</script>
