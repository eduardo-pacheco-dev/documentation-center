<?php

namespace App\Http\Requests\Admin;

use App\Enums\WorkOrderItemStatus;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreWorkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', WorkOrder::class) === true;
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
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'erb_id' => [
                'nullable',
                'integer',
                'prohibits:radio_link_id',
                Rule::exists('erbs', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'radio_link_id' => [
                'nullable',
                'integer',
                'prohibits:erb_id',
                Rule::exists('radio_links', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'client_os' => ['nullable', 'string', 'max:60'],
            'oc' => ['nullable', 'string', 'max:60'],
            'client_orders' => ['nullable', 'array'],
            'client_orders.*.number' => ['required', 'string', 'max:60'],
            'client_orders.*.description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', new Enum(WorkOrderPriority::class)],
            'status' => ['nullable', new Enum(WorkOrderStatus::class)],
            'opened_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:opened_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.catalog_item_id' => [
                'required',
                'integer',
                Rule::exists('catalog_items', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'items.*.status' => ['nullable', new Enum(WorkOrderItemStatus::class)],
            'items.*.client_os' => ['nullable', 'string', 'max:60'],
        ];
    }
}
