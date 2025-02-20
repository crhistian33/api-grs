<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class WorkerRequest extends FormRequest
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
                Rule::unique('workers')->ignore($this->route('worker')),
            ],
            'dni' => [
                'required',
                'max:8',
                Rule::unique('workers')->ignore($this->route('worker')),
            ],
            'birth_date' => 'required',
            'type_worker_id' => 'required',
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
            'name.required' => 'El nombre es requerido',
            'name.unique' => 'El nombre ingresado ya existe',
            'dni.required' => 'El DNI es requerido',
            'dni.unique' => 'El DNI ingresado ya existe',
            'dni.max' => 'El DNI puede tener hasta 8 dígitos',
            'birth_date.required' => 'La fecha de nacimiento es requerida',
            'type_worker_id.required' => 'El tipo de trabajador es requerido',
            'company_id.required' => 'La empresa es requerida',
            'created_by.required' => 'El usuario es requerido',
        ];
    }
}
