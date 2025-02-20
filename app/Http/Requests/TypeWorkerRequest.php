<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class TypeWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                Rule::unique('type_workers')->ignore($this->route('typeworker')),
            ],
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
            'name.required' => 'El nombre es requerido',
            'name.unique' => 'El nombre ingresado ya existe',
            'created_by.required' => 'El usuario es requerido',
        ];
    }
}
