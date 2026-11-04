<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseScanDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'receipt' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required' => 'File nota wajib diupload.',
            'receipt.file' => 'File yang dikirim tidak valid.',
            'receipt.mimes' => 'Format file harus JPG, JPEG, PNG, WEBP, atau PDF.',
            'receipt.max' => 'Ukuran file maksimal 10 MB.',
        ];
    }
}
