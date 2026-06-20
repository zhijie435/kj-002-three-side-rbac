<?php

namespace Shearerline\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShearerlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:shearers,code',
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
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
            'code.required' => '剪切线编码不能为空',
            'code.unique' => '剪切线编码已存在',
            'name.required' => '剪切线名称不能为空',
            'type.required' => '剪切线类型不能为空',
        ];
    }
}
