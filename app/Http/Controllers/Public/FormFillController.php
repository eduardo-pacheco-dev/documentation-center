<?php

namespace App\Http\Controllers\Public;

use App\Exceptions\UploadStorageException;
use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as LaravelValidator;
use Illuminate\View\View;
use Throwable;

class FormFillController extends Controller
{
    /**
     * Maximum number of images accepted by a single image field.
     */
    private const MAX_IMAGES = 5;

    /**
     * Maximum size of each image in kilobytes.
     */
    private const MAX_IMAGE_KILOBYTES = 15360;

    /**
     * Maximum number of files accepted by a single file field.
     */
    private const MAX_FILES = 5;

    /**
     * Maximum size of each file in kilobytes.
     */
    private const MAX_FILE_KILOBYTES = 30720;

    /**
     * How many times an upload is retried before the submission fails.
     */
    private const UPLOAD_ATTEMPTS = 3;

    /**
     * Show the public form filling page.
     */
    public function show(Form $form): View
    {
        $form->load('project:id,code,name');

        $response = $this->singleResponse($form);
        $steps = $this->responseSteps($form->steps ?? [], $response);

        return view('public.forms.fill', [
            'form' => $form,
            'steps' => $steps,
            'response' => $response,
        ]);
    }

    /**
     * Store the answers submitted through the public link.
     */
    public function store(Request $request, Form $form): RedirectResponse
    {
        $structure = $form->steps ?? [];
        $response = $this->singleResponse($form);
        $steps = $this->responseSteps($structure, $response);

        $validator = Validator::make(
            $request->all(),
            $this->rules($structure),
            $this->messages(),
            $this->attributes($structure),
        );

        $validator->after(function (LaravelValidator $validator) use ($request, $steps): void {
            foreach ($steps as $step) {
                foreach ($step['fields'] ?? [] as $field) {
                    $type = $field['type'] ?? null;

                    if (! in_array($type, ['image', 'file'], true)) {
                        continue;
                    }

                    $fieldId = (string) ($field['id'] ?? '');
                    $label = ($field['label'] ?? null) ?: $fieldId;
                    $uploads = $this->uploadedFiles($request->file('answers.'.$fieldId));

                    if ($type === 'image') {
                        $uploads = array_merge(
                            $uploads,
                            $this->uploadedFiles($request->file('camera_answers.'.$fieldId)),
                        );
                    }

                    $existing = $type === 'image'
                        ? ($field['images'] ?? [])
                        : ($field['files'] ?? []);

                    if (($field['required'] ?? false) && $existing === [] && $uploads === []) {
                        $validator->errors()->add(
                            'answers.'.$fieldId,
                            'O campo "'.$label.'" é obrigatório.',
                        );
                    }

                    if ($type === 'image' && count($existing) + count($uploads) > self::MAX_IMAGES) {
                        $validator->errors()->add(
                            'answers.'.$fieldId,
                            'O campo "'.$label.'" aceita no máximo '.self::MAX_IMAGES.' imagens.',
                        );
                    }

                    if ($type === 'file' && count($existing) + count($uploads) > self::MAX_FILES) {
                        $validator->errors()->add(
                            'answers.'.$fieldId,
                            'O campo "'.$label.'" aceita no máximo '.self::MAX_FILES.' arquivos.',
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

        try {
            $filledSteps = $this->applyAnswers($form, $steps, $answers, $request);
        } catch (UploadStorageException $exception) {
            report($exception);

            return redirect()
                ->to(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
                ->withInput()
                ->withErrors([
                    'answers.'.$exception->fieldId => 'Não foi possível salvar o arquivo enviado. Tente novamente.',
                ]);
        }

        DB::transaction(function () use ($form, $request, $response, $status, $filledSteps): void {
            if ($response !== null) {
                $response->update([
                    'status' => $status,
                    'steps' => $filledSteps,
                    'submitted_at' => now(),
                ]);
            } else {
                $form->responses()->create([
                    'user_id' => $request->user()?->getKey(),
                    'status' => $status,
                    'steps' => $filledSteps,
                    'submitted_at' => now(),
                ]);
            }

            $form->update([
                'status' => $status,
                'steps' => $filledSteps,
            ]);
        });

        return redirect()
            ->to(URL::signedRoute('public.forms.fill', ['form' => $form->getKey()]))
            ->with('status', $response !== null
                ? 'Resposta atualizada com sucesso.'
                : 'Resposta enviada com sucesso.');
    }

    /**
     * Get the response that is updated on every submission for single response forms.
     */
    private function singleResponse(Form $form): ?FormResponse
    {
        if ($form->allows_multiple_responses) {
            return null;
        }

        return $form->responses()
            ->latest('submitted_at')
            ->latest('id')
            ->first();
    }

    /**
     * Build the steps that should be shown for the current submission.
     *
     * Single response forms keep the answers of the existing response and follow
     * the current form structure, so new questions are also displayed.
     *
     * @param  array<int, array<string, mixed>>  $structure
     * @return array<int, array<string, mixed>>
     */
    private function responseSteps(array $structure, ?FormResponse $response): array
    {
        $steps = $this->blankSteps($structure);

        if ($response === null) {
            return $steps;
        }

        $previousFields = collect($response->steps ?? [])
            ->flatMap(fn (array $step) => $step['fields'] ?? [])
            ->filter(fn (array $field) => filled($field['id'] ?? null))
            ->keyBy(fn (array $field) => (string) $field['id']);

        foreach ($steps as $stepIndex => $step) {
            foreach ($step['fields'] ?? [] as $fieldIndex => $field) {
                $fieldId = (string) ($field['id'] ?? '');
                $previous = $fieldId !== '' ? $previousFields->get($fieldId) : null;

                if ($previous === null) {
                    continue;
                }

                foreach (['value', 'images', 'files', 'fileNames', 'imageDecisions'] as $key) {
                    if (array_key_exists($key, $previous)) {
                        $steps[$stepIndex]['fields'][$fieldIndex][$key] = $previous[$key];
                    }
                }

                if (array_key_exists('status', $previous)) {
                    $steps[$stepIndex]['fields'][$fieldIndex]['status'] = $previous['status'];
                }
            }
        }

        return $steps;
    }

    /**
     * Build a blank copy of the form structure, without the answers of previous submissions.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @return array<int, array<string, mixed>>
     */
    private function blankSteps(array $steps): array
    {
        foreach ($steps as $stepIndex => $step) {
            foreach ($step['fields'] ?? [] as $fieldIndex => $field) {
                unset(
                    $steps[$stepIndex]['fields'][$fieldIndex]['value'],
                    $steps[$stepIndex]['fields'][$fieldIndex]['images'],
                    $steps[$stepIndex]['fields'][$fieldIndex]['files'],
                    $steps[$stepIndex]['fields'][$fieldIndex]['fileNames'],
                    $steps[$stepIndex]['fields'][$fieldIndex]['imageDecisions'],
                );

                $steps[$stepIndex]['fields'][$fieldIndex]['status'] = 'pendente';
            }
        }

        return $steps;
    }

    /**
     * Build the validation rules from the form structure.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @return array<string, array<int, mixed>>
     */
    private function rules(array $steps): array
    {
        $rules = [
            'answers' => ['nullable', 'array'],
            'camera_answers' => ['nullable', 'array'],
        ];

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
                    $rules[$key] = ['nullable', 'array', 'max:'.self::MAX_IMAGES];
                    $rules[$key.'.*'] = ['image', 'max:'.self::MAX_IMAGE_KILOBYTES];
                    $rules['camera_answers.'.$fieldId] = ['nullable', 'array', 'max:'.self::MAX_IMAGES];
                    $rules['camera_answers.'.$fieldId.'.*'] = ['image', 'max:'.self::MAX_IMAGE_KILOBYTES];

                    continue;
                }

                if ($type === 'file') {
                    $rules[$key] = ['nullable', 'array', 'max:'.self::MAX_FILES];
                    $rules[$key.'.*'] = ['file', 'max:'.self::MAX_FILE_KILOBYTES];

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
                $attributes['camera_answers.'.$fieldId] = $label;
                $attributes['camera_answers.'.$fieldId.'.*'] = $label;
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
            'file' => 'O campo :attribute deve conter um arquivo válido.',
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
                    $uploads = array_merge(
                        $this->uploadedFiles($request->file('answers.'.$fieldId)),
                        $this->uploadedFiles($request->file('camera_answers.'.$fieldId)),
                    );

                    $steps[$stepIndex]['fields'][$fieldIndex] = $this->applyImages(
                        $form,
                        $field,
                        $uploads,
                    );

                    continue;
                }

                if (($field['type'] ?? null) === 'file') {
                    $steps[$stepIndex]['fields'][$fieldIndex] = $this->applyFiles(
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
     * Normalize a request file value into a zero-indexed list of uploads.
     *
     * @return array<int, UploadedFile>
     */
    private function uploadedFiles(mixed $uploads): array
    {
        $uploads = is_array($uploads) ? $uploads : ($uploads === null ? [] : [$uploads]);

        return array_values(array_filter(
            $uploads,
            fn (mixed $upload) => $upload instanceof UploadedFile,
        ));
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
        $fieldId = (string) ($field['id'] ?? '');
        $label = (string) (($field['label'] ?? null) ?: $fieldId);

        foreach ($uploads as $upload) {
            if (! $upload instanceof UploadedFile) {
                continue;
            }

            $path = $this->storeUpload($form, $upload, $fieldId, $label);
            $images[] = Storage::disk('public')->url($path);
        }

        $field['images'] = array_values($images);
        $field['status'] = $images !== [] ? 'enviado' : 'pendente';

        return $field;
    }

    /**
     * Append the uploaded files to the field and update its status.
     *
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    private function applyFiles(Form $form, array $field, mixed $uploads): array
    {
        $files = $field['files'] ?? [];
        $names = $field['fileNames'] ?? [];
        $uploads = is_array($uploads) ? $uploads : ($uploads === null ? [] : [$uploads]);
        $fieldId = (string) ($field['id'] ?? '');
        $label = (string) (($field['label'] ?? null) ?: $fieldId);

        foreach ($uploads as $upload) {
            if (! $upload instanceof UploadedFile) {
                continue;
            }

            $path = $this->storeUpload($form, $upload, $fieldId, $label);
            $files[] = Storage::disk('public')->url($path);
            $names[] = $upload->getClientOriginalName();
        }

        $field['files'] = array_values($files);
        $field['fileNames'] = array_values($names);
        $field['status'] = $files !== [] ? 'enviado' : 'pendente';

        return $field;
    }

    /**
     * Store an uploaded file and verify that it reached the disk.
     *
     * Uploads are retried a few times so transient filesystem errors do not
     * discard a photo or document. If the disk keeps failing, the exception
     * aborts the submission instead of silently dropping the upload.
     */
    private function storeUpload(Form $form, UploadedFile $upload, string $fieldId, string $label): string
    {
        $disk = Storage::disk('public');
        $directory = 'forms/'.$form->getKey();
        $lastException = null;

        for ($attempt = 1; $attempt <= self::UPLOAD_ATTEMPTS; $attempt++) {
            $path = false;

            try {
                $path = $disk->putFile($directory, $upload);
            } catch (Throwable $exception) {
                $lastException = $exception;
            }

            try {
                if (is_string($path) && $path !== '' && $disk->exists($path)) {
                    return $path;
                }
            } catch (Throwable $exception) {
                $lastException = $exception;
            }
        }

        throw new UploadStorageException(
            $fieldId,
            'Não foi possível salvar o arquivo "'.$upload->getClientOriginalName().'" do campo "'.$label.'".',
            $lastException,
        );
    }
}
