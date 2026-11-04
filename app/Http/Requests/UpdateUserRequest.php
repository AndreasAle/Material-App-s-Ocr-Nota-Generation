<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', "unique:users,email,{$userId}"],
            'role'            => ['required', 'in:admin,staff,pimpinan,purchasing,kasir'],
            'whatsapp_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]*$/'],
            'is_active'       => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Nama wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah digunakan user lain.',
            'role.required'         => 'Role wajib dipilih.',
            'role.in'               => 'Role tidak valid.',
            'whatsapp_number.regex' => 'Format nomor WhatsApp tidak valid.',
        ];
    }
}
