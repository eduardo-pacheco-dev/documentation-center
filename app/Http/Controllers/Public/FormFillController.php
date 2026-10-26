<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as LaravelValidator;
use Illuminate\View\View;

class FormFillController extends Controller
{
    /**
     * Show the public form filling page.
     */
    public function show(Form $form): View
    {
        $form->load('project:id,code,name');

        return view('public.forms.fill', [
            'form' => $form,
            'steps' => $form->steps ?? [],
        ]);
    }

    /**
     * Store the answers submitted through the public link.
     */
    public function store(Request $request, Form $form): RedirectResponse
    {
        $steps = $form->steps ?? [];

        $validator = Validator::make(
            $request->all(),
            $this->rules($steps),
            $this->messages(),
            $this->attributes($steps),
        );

        $validator->after(function (LaravelValidator $validator) use ($request, $steps): void {
            foreach ($steps as $step) {
                foreach ($step['fields'] ?? [] as $field) {
                    if (($field['type'] ?? null) !== 'image' || ! ($field['required'] ?? false)) {
                        continue;
                    }

                    $fieldId = (string) ($field['id'] ?? '');
                    $uploads = $request->file('answers.'.$fieldId);
                    $uploads = is_array($uploads) ? array_filter($uploads) : array_filter([$uploads]);

                    if (empty($field['images'] ?? []) && $uploads === []) {
                        $validator->errors()->add(
                            'answers.'.$fieldId,
                            'O campo "'.(($field['label'] ?? null) ?: $fieldId).'" é obrigatório.',
                        );
                    }
                }
            }
        });

        $validated = $validator->validate();
        $answers = is_array($validated['answers'] ?? null) ? $validated['answers'] : [];

        $status = in_array($form->status, ['Pendente', 'Em preenchimento'], true)
            ? 'Preenchido'
            : $form->status;

        $form->update([
            'status' => $status,
            'steps' => $this->applyAnswers($form, $steps, $answers, $request),
        ]);

        return redirect()
            ->to(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
            ->with('status', 'Resposta enviada com sucesso.');
    }

    /**
     * Build the validation rules from the form structure.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @return array<string, array<int, mixed>>
     */
    private function rules(array $steps): array
    {
        $rules = ['answers' => ['nullable', 'array']];

        foreach ($steps as $step) {
            foreach ($step['fields'] ?? [] as $field) {
                $fieldId = (string) ($field['id'] ?? '');

                if ($fieldId === '') {
                    continue;
                }

                $key = 'answers.'.$fieldId;
                $type = $field['type'] ?? 'text';
                $required = (bool) ($field['required'] ?? false);

                if ($type === 'image') {
                    $rules[$key] = ['nullable', 'array', 'max:5'];
                    $rules[$key.'.*'] = ['image', 'max:5120'];

                    continue;
                }

                if ($type === 'checkbox') {
                    $options = $this->options($field);

                    $rules[$key] = [$required ? 'required' : 'nullable', 'array'];

                    if ($options !== []) {
                        $rules[$key.'.*'] = [Rule::in($options)];
                    }

                    continue;
                }

                if (in_array($type, ['radio', 'dropdown'], true)) {
                    $options = $this->options($field);

                    $rules[$key] = [$required ? 'required' : 'nullable'];

                    if ($options !== []) {
                        $rules[$key][] = Rule::in($options);
                    }

                    continue;
                }

                $rules[$key] = match ($type) {
                    'number' => [$required ? 'required' : 'nullable', 'numeric'],
                    'date' => [$required ? 'required' : 'nullable', 'date'],
                    'time' => [$required ? 'required' : 'nullable', 'date_format:H:i'],
                    'scale' => [
                        $required ? 'required' : 'nullable',
                        'integer',
                        'min:'.(int) ($field['scaleMin'] ?? 1),
                        'max:'.(int) ($field['scaleMax'] ?? 5),
                    ],
                    default => [$required ? 'required' : 'nullable', 'string', 'max:5000'],
                };
            }
        }

        return $rules;
    }

    /**
     * Get the valid options for a choice field.
     *
     * @param  array<string, mixed>  $field
     * @return array<int, string>
     */
    private function options(array $field): array
    {
        return collect($field['options'] ?? [])
            ->filter(fn (mixed $option) => is_string($option) && $option !== '')
            ->values()
            ->all();
    }

    /**
     * Translate validation attributes to the field labels.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @return array<string, string>
     */
    private function attributes(array $steps): array
    {
        $attributes = [];

        foreach ($steps as $step) {
            foreach ($step['fields'] ?? [] as $field) {
                $fieldId = (string) ($field['id'] ?? '');

                if ($fieldId === '') {
                    continue;
                }

                $label = ($field['label'] ?? null) ?: $fieldId;
                $attributes['answers.'.$fieldId] = $label;
                $attributes['answers.'.$fieldId.'.*'] = $label;
            }
        }

        return $attributes;
    }

    /**
     * Get the validation messages in Brazilian Portuguese.
     *
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'array' => 'O campo :attribute deve ser uma lista de valores.',
            'string' => 'O campo :attribute deve ser um texto.',
            'numeric' => 'O campo :attribute deve ser um número.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'date' => 'O campo :attribute deve conter uma data válida.',
            'date_format' => 'O campo :attribute deve conter um horário válido.',
            'image' => 'O campo :attribute deve conter uma imagem válida.',
            'max' => 'O campo :attribute excede o limite permitido.',
            'in' => 'O valor selecionado em :attribute é inválido.',
            'min' => 'O valor de :attribute deve ser no mínimo :min.',
        ];
    }

    /**
     * Apply the submitted answers to the form structure.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @param  array<string, mixed>  $answers
     * @return array<int, array<string, mixed>>
     */
    private function applyAnswers(Form $form, array $steps, array $answers, Request $request): array
    {
        foreach ($steps as $stepIndex => $step) {
            foreach ($step['fields'] ?? [] as $fieldIndex => $field) {
                $fieldId = (string) ($field['id'] ?? '');

                if ($fieldId === '') {
                    continue;
                }

                if (($field['type'] ?? null) === 'image') {
                    $steps[$stepIndex]['fields'][$fieldIndex] = $this->applyImages(
                        $form,
                        $field,
                        $request->file('answers.'.$fieldId),
                    );

                    continue;
                }

                $value = $answers[$fieldId] ?? null;

                if (is_array($value)) {
                    $value = implode(', ', array_map(fn (mixed $item) => (string) $item, $value));
                }

                $value = is_string($value) ? trim($value) : (string) ($value ?? '');

                $steps[$stepIndex]['fields'][$fieldIndex]['value'] = $value;
                $steps[$stepIndex]['fields'][$fieldIndex]['status'] = $value !== '' ? 'enviado' : 'pendente';
            }
        }

        return $steps;
    }

    /**
     * Append the uploaded images to the field and update its status.
     *
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    private function applyImages(Form $form, array $field, mixed $uploads): array
    {
        $images = $field['images'] ?? [];
        $uploads = is_array($uploads) ? $uploads : ($uploads === null ? [] : [$uploads]);

        foreach ($uploads as $upload) {
            if (! $upload instanceof UploadedFile) {
                continue;
            }

            $path = $upload->store('forms/'.$form->getKey(), 'public');

            if ($path !== false) {
                $images[] = Storage::disk('public')->url($path);
            }
        }

        $field['images'] = array_values($images);
        $field['status'] = $images !== [] ? 'enviado' : 'pendente';

        return $field;
    }
}
