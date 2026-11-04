<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('material')?->id;

        return [
            'material_category_id' => ['required', 'exists:material_categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'code' => ['required', 'string', 'max:255', Rule::unique('materials', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'default_price' => ['nullable', 'numeric', 'gte:0'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'sell_price' => ['nullable', 'numeric', 'gte:0'],
            'wholesale_price' => ['nullable', 'numeric', 'gte:0'],
            'wholesale_min_qty' => ['nullable', 'numeric', 'gt:0', 'required_with:wholesale_price'],
            'minimum_stock' => ['nullable', 'numeric', 'gte:0'],
            'units' => ['nullable', 'array'],
            'units.*.unit_name' => ['required_with:units.*.factor', 'nullable', 'string', 'max:50', 'distinct:ignore_case'],
            'units.*.factor' => ['required_with:units.*.unit_name', 'nullable', 'numeric', 'gt:0'],
            'units.*.sell_price' => ['nullable', 'numeric', 'gte:0'],
            'specification' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}