<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRabPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'              => ['required', 'exists:projects,id'],
            'name'                    => ['required', 'string', 'max:255'],
            'period_start'            => ['required', 'date'],
            'period_end'              => ['nullable', 'date', 'after_or_equal:period_start'],
            'description'             => ['nullable', 'string'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.material_id'     => ['nullable', 'exists:materials,id'],
            'items.*.material_name_snapshot' => ['required', 'string', 'max:255'],
            'items.*.unit_name_snapshot'     => ['required', 'string', 'max:255'],
            'items.*.qty_planned'     => ['required', 'numeric', 'gt:0'],
            'items.*.price_planned'   => ['required', 'numeric', 'gte:0'],
            'items.*.notes'           => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'project_id'   => 'proyek',
            'name'         => 'nama RAB',
            'period_start' => 'periode mulai',
            'period_end'   => 'periode selesai',
            'items'        => 'item RAB',
        ];
    }
}
