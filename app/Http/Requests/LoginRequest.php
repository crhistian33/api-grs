<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El email es requerido',
            'email.email' => 'El email no es válido',
            'password.required' => 'La contraseeña es requerida',
            'password.string' => 'La contraseña no es válida',
        ];
    }
}
