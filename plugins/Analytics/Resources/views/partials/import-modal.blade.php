@php
    $importModalOpen = session('import_started') === true
        || ($errors->any() && in_array(old('modal'), ['import-fams', 'import-afl'], true));
@endphp

<x-ui.modal
    id="analytics-import-modal"
    title="Importar dados"
    description="Envie uma planilha para atualizar os registros de FAMs ou AFL. Reimportar cria uma nova versão quando houver alteração."
    max-width="max-w-3xl"
    :data-open="$importModalOpen ? true : null"
>
    @if ($errors->has('import') || $errors->has('file'))
        <div data-modal-error class="space-y-3">
            @error('import')
                <x-analytics::error-summary
                    title="Não foi possível importar a planilha."
                    :lines="explode(chr(10), $message)"
                />
            @enderror

            @error('file')
                <x-ui.alert tone="error">{{ $message }}</x-ui.alert>
            @enderror
        </div>
    @endif

    <div class="space-y-4">
        @if (is_array($recordsSummary))
            <x-analytics::import-form
                title="Importar dados FAMs"
                description="OC, OS, fornecedor, etapa, elemento, endereço, node name, localização e status."
                action="analytics.records.import"
                template-route="analytics.records.template"
                submit-label="Importar FAMs"
                file-id="records-file"
                max-size="10 MB"
                modal-target="import-fams"
                :disabled="$hasPendingImports"
                :summary="[
                    $recordsSummary['total'].' registros',
                    $recordsSummary['versions'].' versões',
                    $recordsSummary['in_period'].' no período',
                ]"
                :days="$days"
                :scope="$scope"
            />
        @endif

        @if (is_array($aflSummary))
            <x-analytics::import-form
                title="Importar dados AFL"
                description="Layout AFL completo (108 colunas): acesso, atividade, ativação, documentação, aceitação e faturamento."
                action="analytics.afl.import"
                template-route="analytics.afl.template"
                submit-label="Importar AFL"
                file-id="afl-file"
                tone="rose"
                max-size="20 MB"
                modal-target="import-afl"
                :disabled="$hasPendingImports"
                :summary="[
                    $aflSummary['total'].' registros',
                    $aflSummary['versions'].' versões',
                    $aflSummary['in_period'].' no período',
                ]"
                :days="$days"
                :scope="$scope"
            />
        @endif
    </div>

    <div data-import-history>
        @include('analytics::partials.import-history', ['imports' => $recentImports])
    </div>
</x-ui.modal>
