<?php

namespace Shearerline\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShearerlineTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shearerline_id' => 'nullable|integer|exists:shearers,id',
            'order_no' => 'string|max:50',
            'product_name' => 'string|max:200',
            'quantity' => 'integer|min:0|max:99999',
            'priority' => 'string|in:low,medium,high,urgent',
            'status' => 'string|in:pending,assigned,processing,completed,cancelled',
            'operator_id' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'integer|min:0',
        ];
    }
}
