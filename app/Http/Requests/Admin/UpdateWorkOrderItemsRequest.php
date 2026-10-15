<?php

namespace App\Http\Requests\Admin;

use App\Enums\WorkOrderItemStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateWorkOrderItemsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('workOrder')) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.catalog_item_id' => [
                'required',
                'integer',
                Rule::exists('catalog_items', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'items.*.status' => ['nullable', new Enum(WorkOrderItemStatus::class)],
        ];
    }
}
