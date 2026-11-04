<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_date' => ['required', 'date'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'note_number' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,submitted,posted'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.material_code' => ['nullable', 'string', 'max:255'],
            'items.*.material_id' => ['nullable', 'exists:materials,id'],
            'items.*.material_name_snapshot' => ['required', 'string', 'max:255'],
            'items.*.unit_name_snapshot' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.price' => ['required', 'numeric', 'gte:0'],
            'items.*.notes' => ['nullable', 'string'],

            // Toko: barang datang bersama nota, lunas di tempat, buat barang baru dari nota
            'mark_received' => ['nullable', 'boolean'],
            'paid_in_full' => ['nullable', 'boolean'],
            'payment_method' => ['nullable', 'in:cash,transfer,giro,cek'],
            'auto_create_missing_materials' => ['nullable', 'boolean'],

                'attachments' => ['nullable', 'array'],
                'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }
}