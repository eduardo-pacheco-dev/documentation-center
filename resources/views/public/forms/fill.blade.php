<x-layouts.public :title="'Preencher '.$form->code" :with-action-bar="! empty($steps)">
    @php
        $questionFields = collect($steps)->flatMap(fn (array $step) => $step['fields'] ?? []);
        $totalQuestions = $questionFields->count();
        $answeredQuestions = $questionFields->filter(
            fn (array $field) => filled($field['value'] ?? null)
                || ! empty($field['images'] ?? [])
                || ! empty($field['files'] ?? []),
        )->count();
        $initialPercent = $totalQuestions > 0
            ? (int) round(($answeredQuestions / $totalQuestions) * 100)
            : 0;

        $sectionCount = count($steps);
        $initialStep = 0;

        foreach ($steps as $index => $step) {
            foreach ($step['fields'] ?? [] as $field) {
                $fieldId = (string) ($field['id'] ?? '');

                if ($fieldId !== '' && ($errors->has('answers.'.$fieldId) || $errors->has('camera_answers.'.$fieldId))) {
                    $initialStep = $index;
                    break 2;
                }
            }
        }
    @endphp

    <div class="space-y-4">
        <x-public.forms.header
            :form="$form"
            :total-questions="$totalQuestions"
            :answered-questions="$answeredQuestions"
            :percent="$initialPercent"
        />

        @if (session('status'))
            <x-ui.alert tone="success">{{ session('status') }}</x-ui.alert>
        @endif

        @if (request()->boolean('upload_error'))
            <x-ui.alert tone="error">
                Os arquivos enviados são maiores do que o servidor aceita. Reduza o tamanho das imagens ou documentos e envie novamente.
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert tone="error">
                Não foi possível enviar as respostas. Corrija os itens destacados e tente novamente. As imagens e os arquivos já enviados foram mantidos.
            </x-ui.alert>
        @endif

        @if (empty($steps))
            <x-public.forms.empty-state>
                Este formulário ainda não possui perguntas.
            </x-public.forms.empty-state>
        @else
            <x-public.forms.wizard
                :form="$form"
                :steps="$steps"
                :initial-step="$initialStep"
                :section-count="$sectionCount"
                :has-response="(bool) $response"
            />
        @endif
    </div>

    <x-public.forms.image-modal />
</x-layouts.public>
