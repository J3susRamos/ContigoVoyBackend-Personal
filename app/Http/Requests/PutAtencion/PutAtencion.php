<?php

namespace App\Http\Requests\PutAtencion;

use Illuminate\Foundation\Http\FormRequest;

class PutAtencion extends FormRequest
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
            'motivoConsulta' => 'sometimes|string|max:255',
            'formaContacto' => 'sometimes|string|max:100',
            'diagnostico' => 'sometimes|string',
            'tratamiento' => 'sometimes|string',
            'observacion' => 'sometimes|string',
            'ultimosObjetivos' => 'sometimes|string',
            'comentario' => 'sometimes|string',
            'fechaAtencion' => 'sometimes|date',
        ];
    }
}