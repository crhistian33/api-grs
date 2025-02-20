<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class AssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            'unit_shift_id' => 'required',
            'user_id' => 'required',
        ];
    }

    public function getAllFields()
    {
        return array_filter(
            $this->all(),
            function ($value) {
                return $value !== null && $value !== '';
            }
        );
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $messages = [];

        foreach ($errors->all() as $message) {
            $messages[] = [$message];
        }

        throw new HttpResponseException(response()->json([
            'success'     => false,
            'status_code' => 422,
            'message'     => $messages,
        ], 422));
    }

    public function messages(): array
    {
        return [
            'unit_shift_id.required' => 'La unidad por turno es requerida',
            'user_id.required' => 'El usuario es requerido',
        ];
    }
}
