<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkerAssignsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'today' => 'required',
            'unit_shift_id' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'today.required' => 'La fecha es requerida',
            'unit_shift_id.required' => 'La unidad por turno es requerida',
        ];
    }
}
