<?php

namespace Shearerline\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShearerlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shearerlineId = $this->route('id');

        return [
            'code' => 'string|max:50|unique:shearers,code,' . $shearerlineId,
            'name' => 'string|max:100',
            'type' => 'string|max:50',
            'location' => 'nullable|string|max:100',
            'status' => 'string|in:idle,running,maintenance,error,disabled',
            'max_capacity' => 'integer|min:0|max:99999',
            'current_load' => 'integer|min:0|max:99999',
            'operator_id' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => '剪切线编码已存在',
        ];
    }
}
