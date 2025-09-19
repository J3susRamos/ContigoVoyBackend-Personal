<?php

namespace App\Http\Requests\PostAtencion;

use Illuminate\Foundation\Http\FormRequest;

class PostAtencion extends FormRequest
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
            'motivoConsulta' => 'required|string|max:255',
            'formaContacto' => 'required|string|max:100',
            'diagnostico' => 'required|string',
            'tratamiento' => 'required|string',
            'observacion' => 'required|string',
            'ultimosObjetivos' => 'required|string',
            'idEnfermedad' => 'required|exists:enfermedades,idEnfermedad',
            'comentario' => 'nullable|string',
            'documentosAdicionales' => 'nullable|string',
            'fechaAtencion' => 'required|date',
            'descripcion' => 'nullable|string',
        ];
    }
}
