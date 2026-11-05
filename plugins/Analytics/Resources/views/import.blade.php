@php
    $columns = [
        'OC', 'OS', 'Data Criação', 'Fornecedor', 'Fornecedor Fiscalização',
        'Tipo Projeto', 'Etapa Processo', 'Tipo Elemento', 'Endereço Atendimento',
        'Cidade', 'Estado', 'Node Name', 'Localização', 'Operador', 'Status',
    ];
@endphp

<x-layouts.admin title="Importar Analytics">
    <x-ui.page-header
        category="Operações"
        title="Importar planilha"
        description="Envie um arquivo CSV ou Excel com as ordens de serviço. Registros existentes são atualizados pelo número da OS."
        icon="arrow-up-tray"
    >
        <x-ui.action :href="route('admin.analytics.index')" icon="chevron-left" variant="secondary">
            Voltar
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 grid gap-4 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
            <form method="POST" action="{{ route('admin.analytics.import.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label for="analytics-file" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Arquivo da planilha
                    </label>
                    <input
                        id="analytics-file"
                        type="file"
                        name="file"
                        accept=".csv,.txt,.xlsx,.xls"
                        required
                        class="mt-1.5 block w-full cursor-pointer rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white transition hover:file:bg-indigo-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300"
                    >

                    @error('file')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Formatos aceitos: CSV, XLSX e XLS, até 10 MB.
                    </p>
                </div>

                <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50/60 p-4 dark:border-white/15 dark:bg-white/[0.02]">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Como funciona</p>
                    <ul class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <li>• A primeira linha deve conter os cabeçalhos das colunas.</li>
                        <li>• A OS é usada como chave: registros já existentes são atualizados.</li>
                        <li>• Datas aceitas: <code class="rounded bg-gray-100 px-1 py-0.5 dark:bg-white/10">AAAA-MM-DD</code>, <code class="rounded bg-gray-100 px-1 py-0.5 dark:bg-white/10">DD/MM/AAAA</code> ou data do Excel.</li>
                        <li>• Todos os erros de validação são exibidos antes de qualquer gravação; nada é importado parcialmente.</li>
                    </ul>
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.action type="submit" icon="arrow-up-tray">
                        Importar planilha
                    </x-ui.action>

                    <a href="{{ route('admin.analytics.index') }}" class="text-sm font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Cancelar
                    </a>
                </div>
            </form>
        </section>

        <aside class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="document-text" class="h-4 w-4" />
                </span>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Colunas esperadas</h2>
            </div>

            <ul class="mt-4 space-y-1.5">
                @foreach ($columns as $column)
                    <li class="rounded-lg border border-gray-100 px-2.5 py-1.5 font-mono text-[11px] text-gray-600 dark:border-white/10 dark:text-gray-300">
                        {{ $column }}
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</x-layouts.admin>
