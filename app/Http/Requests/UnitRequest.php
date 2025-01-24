<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
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
                Rule::unique('units')->ignore($this->route('unit')),
            ],
            'name' => [
                'required',
                Rule::unique('units')->ignore($this->route('unit')),
            ],
            'center_id' => 'required',
            'customer_id' => 'required',
            'min_assign' => 'required',
            'user_id' => 'required',
        ];
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
            'center_id.required' => 'El centro de costo es requerido',
            'customer_id.required' => 'El cliente es requerido',
            'min_assign.required' => 'El número de trabajadores a asignar es requerido',
            'user_id.required' => 'El usuario es requerido',
        ];
    }
}
