<x-layouts.admin title="Importar sites">
    @php
        $fields = [
            ['label' => 'Site ID', 'name' => 'site_id', 'required' => true],
            ['label' => 'End ID', 'name' => 'end_id', 'required' => false],
            ['label' => 'OC', 'name' => 'oc', 'required' => false],
            ['label' => 'OS FAM Entrega', 'name' => 'os_fam_entrega', 'required' => false],
            ['label' => 'OS FAM Instalação', 'name' => 'os_fam_instalacao', 'required' => false],
        ];

        foreach (config('nokia.checklists', []) as $name => $label) {
            $fields[] = ['label' => $label, 'name' => $name, 'required' => false];
        }
    @endphp

    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
        <a href="{{ route('admin.nokia.sites.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Sites Nokia</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <span class="truncate text-gray-400 dark:text-gray-500">Importar sites</span>
    </nav>

    <x-ui.page-header
        category="Nokia"
        title="Importar sites"
        description="Envie uma planilha Excel ou CSV para cadastrar vários sites de uma só vez."
        icon="arrow-up-tray"
        class="mt-4"
    >
        <x-ui.action :href="route('admin.nokia.sites.import.template')" icon="arrow-down-tray" variant="secondary">
            Baixar modelo Excel
        </x-ui.action>

        <x-ui.action :href="route('admin.nokia.sites.index')" icon="chevron-left" variant="ghost">
            Voltar
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 grid items-start gap-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
        <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="arrow-up-tray" class="h-5 w-5" />
                </span>

                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Enviar arquivo</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">O processamento é feito de uma vez: se alguma linha estiver inválida, nenhum site é importado.</p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.nokia.sites.import.store') }}"
                enctype="multipart/form-data"
                class="mt-5"
                x-data="{ fileName: '' }"
            >
                @csrf

                <label
                    for="import-file"
                    class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-white px-5 py-10 text-center transition hover:border-indigo-300 dark:border-gray-700 dark:bg-[#171716] dark:hover:border-indigo-500/40"
                >
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                        <x-icon name="arrow-up-tray" class="h-5 w-5" />
                    </span>

                    <span class="mt-3 text-sm font-semibold text-gray-800 dark:text-gray-100">Selecionar planilha</span>
                    <span class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="! fileName">Excel (XLSX/XLS) ou CSV · até 5 MB · máximo de {{ \Plugins\Nokia\Support\NokiaSiteImporter::MAX_SITES }} sites</span>
                    <span class="mt-1 max-w-full truncate text-xs font-medium text-indigo-600 dark:text-indigo-400" x-show="fileName" x-text="fileName"></span>

                    <input
                        id="import-file"
                        type="file"
                        name="file"
                        accept=".csv,.txt,.xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv,text/plain"
                        class="sr-only"
                        x-on:change="fileName = $event.target.files[0]?.name ?? ''"
                    >
                </label>

                @error('file')
                    <x-ui.field-error class="mt-3">{{ $message }}</x-ui.field-error>
                @enderror

                <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                    <x-ui.action :href="route('admin.nokia.sites.index')" variant="ghost">
                        Cancelar
                    </x-ui.action>

                    <x-ui.action type="submit" icon="arrow-up-tray">
                        Importar sites
                    </x-ui.action>
                </div>
            </form>
        </section>

        <aside class="space-y-4">
            <section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
                <div class="flex items-center gap-2">
                    <x-icon name="exclamation-circle" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Antes de importar</h2>
                </div>

                <ul class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    <li class="flex gap-2">
                        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                        <span>Use o modelo Excel e preencha a aba <strong class="font-semibold text-gray-900 dark:text-white">Sites</strong> a partir da linha 2.</span>
                    </li>
                    <li class="flex gap-2">
                        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                        <span>A coluna <code class="rounded bg-gray-100 px-1 py-0.5 text-[11px] dark:bg-white/10">Site ID</code> é obrigatória.</span>
                    </li>
                    <li class="flex gap-2">
                        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                        <span>No checklist, use <strong class="font-semibold text-gray-900 dark:text-white">Sim</strong> ou <strong class="font-semibold text-gray-900 dark:text-white">Não</strong>.</span>
                    </li>
                    <li class="flex gap-2">
                        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                        <span>Também aceita CSV exportado do Excel.</span>
                    </li>
                    <li class="flex gap-2">
                        <x-icon name="exclamation-triangle" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
                        <span>Limite de <strong class="font-semibold text-gray-900 dark:text-white">{{ \Plugins\Nokia\Support\NokiaSiteImporter::MAX_SITES }} sites</strong> por arquivo.</span>
                    </li>
                </ul>
            </section>

            <section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Colunas do modelo</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Baixe o modelo para garantir os nomes corretos. Colunas extras são ignoradas.</p>

                <div class="mt-4 flex flex-wrap gap-1.5">
                    @foreach ($fields as $field)
                        <span class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-2 py-1 text-[10px] text-gray-600 dark:bg-white/5 dark:text-gray-300" title="{{ $field['name'] }}">
                            {{ $field['label'] }}

                            @if ($field['required'])
                                <span class="font-sans font-semibold text-red-500" title="Obrigatório">*</span>
                            @endif
                        </span>
                    @endforeach
                </div>

                <x-ui.action :href="route('admin.nokia.sites.import.template')" icon="arrow-down-tray" variant="secondary" size="sm" class="mt-4">
                    Baixar modelo Excel
                </x-ui.action>
            </section>
        </aside>
    </div>
</x-layouts.admin>
