<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                Rule::unique('customers')->ignore($this->route('customer')),
            ],
            'name' => [
                'required',
                Rule::unique('customers')->ignore($this->route('customer')),
            ],
            'company_id' => 'required',
            'created_by' => [
                Rule::when($this->isMethod('POST'), [
                    'required',
                ]),
            ]
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
            'code.required' => 'El código es requerido',
            'code.unique' => 'El código ingresado ya existe',
            'name.required' => 'El nombre es requerido',
            'name.unique' => 'El nombre ingresado ya existe',
            'company_id.required' => 'La empresa es requerida',
            'created_by.required' => 'El usuario es requerido',
        ];
    }
}
