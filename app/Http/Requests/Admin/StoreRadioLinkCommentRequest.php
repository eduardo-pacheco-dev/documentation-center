<?php

namespace App\Http\Requests\Admin;

use App\Models\RadioLink;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRadioLinkCommentRequest extends FormRequest
{
    /**
     * The radio link the request acts on.
     */
    public function radioLink(): RadioLink
    {
        return $this->route('radioLink');
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->radioLink()) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:4000'],
        ];
    }
}
