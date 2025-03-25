<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:dateFrom',
        ];
    }

    public function messages(): array
    {
        return [
            'date_from.required' => 'La fecha desde es requerida',
            'date_from.date' => 'La fecha desde, no tiene el formato correcto',
            'date_to.required' => 'La fecha hasta es requerida',
            'date_from.date' => 'La fecha hasta, no tiene el formato correcto',
            'date_to.after_or_equal' => 'La fecha hasta debe ser mayor o igual a la fecha desde'
        ];
    }
}
