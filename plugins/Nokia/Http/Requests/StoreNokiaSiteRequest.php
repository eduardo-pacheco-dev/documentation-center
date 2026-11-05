<?php

namespace Plugins\Nokia\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNokiaSiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Normalize the checklist values before validation.
     */
    protected function prepareForValidation(): void
    {
        $values = [];

        foreach (array_keys(config('nokia.checklists', [])) as $field) {
            $values[$field] = (bool) filter_var($this->input($field, false), FILTER_VALIDATE_BOOLEAN);
        }

        $this->merge($values);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_id' => ['required', 'string', 'max:100'],
            'end_id' => ['nullable', 'string', 'max:100'],
            'oc' => ['nullable', 'string', 'max:100'],
            'os_fam_entrega' => ['nullable', 'string', 'max:100'],
            'os_fam_instalacao' => ['nullable', 'string', 'max:100'],
            'checklist_tarefas_enviadas' => ['boolean'],
            'checklist_fam_entrega' => ['boolean'],
            'checklist_fam_instalacao' => ['boolean'],
            'checklist_fam_panoramica' => ['boolean'],
        ];
    }
}
