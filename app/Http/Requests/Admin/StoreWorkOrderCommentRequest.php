<?php

namespace App\Http\Requests\Admin;

use App\Models\WorkOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkOrderCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->workOrder()) === true;
    }

    /**
     * The work order the request acts on.
     */
    public function workOrder(): WorkOrder
    {
        return $this->route('workOrder');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => [
                'nullable',
                'string',
                'max:4000',
                Rule::requiredIf(fn (): bool => blank($this->file('images'))),
            ],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
        ];
    }

    /**
     * Get the validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => 'Escreva um comentário ou anexe ao menos uma imagem.',
            'body.max' => 'O comentário não pode ter mais de 4000 caracteres.',
            'images.array' => 'Não foi possível ler as imagens enviadas.',
            'images.max' => 'Você pode anexar no máximo 10 imagens por comentário.',
            'images.*.file' => 'Cada anexo precisa ser um arquivo de imagem válido.',
            'images.*.image' => 'Apenas imagens JPG, PNG, WEBP ou GIF são aceitas.',
            'images.*.mimes' => 'Apenas imagens JPG, PNG, WEBP ou GIF são aceitas.',
            'images.*.max' => 'Cada imagem deve ter no máximo 10 MB.',
        ];
    }
}
