<?php

namespace App\Http\Requests\PostRegistroFamiliar;

use Illuminate\Foundation\Http\FormRequest;

class PostRegistroFamiliar extends FormRequest
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
            'nombre_madre' => 'nullable|string|max:250',
            'estado_madre' => 'required|string|max:250',
            'nombre_padre' => 'nullable|string|max:250',
            'estado_padre' => 'required|string|max:250',
            'nombre_apoderado' => 'nullable|string|max:250',
            'estado_apoderado' => 'nullable|string|max:250',
            'cantidad_hijos' => 'required|integer|min:0',
            'cantidad_hermanos' => 'required|integer|min:0',
            'integracion_familiar' => 'required|string|max:450',
            'historial_familiar' => 'required|string|max:400',
        ];
    }

    public function messages(): array
{
    return [
        'estado_madre.required' => 'El campo estado de la madre es obligatorio.',
        'estado_madre.string' => 'El campo estado de la madre debe ser texto.',
        'estado_madre.max' => 'El campo estado de la madre no debe exceder los 250 caracteres.',

        'estado_padre.required' => 'El campo estado del padre es obligatorio.',
        'estado_padre.string' => 'El campo estado del padre debe ser texto.',
        'estado_padre.max' => 'El campo estado del padre no debe exceder los 250 caracteres.',

        'cantidad_hijos.required' => 'Debe especificar la cantidad de hijos.',
        'cantidad_hijos.integer' => 'La cantidad de hijos debe ser un número entero.',
        'cantidad_hijos.min' => 'La cantidad de hijos no puede ser negativa.',

        'cantidad_hermanos.required' => 'Debe especificar la cantidad de hermanos.',
        'cantidad_hermanos.integer' => 'La cantidad de hermanos debe ser un número entero.',
        'cantidad_hermanos.min' => 'La cantidad de hermanos no puede ser negativa.',

        'integracion_familiar.required' => 'El campo integración familiar es obligatorio.',
        'integracion_familiar.string' => 'El campo integración familiar debe ser texto.',
        'integracion_familiar.max' => 'El campo integración familiar no debe exceder los 450 caracteres.',

        'historial_familiar.required' => 'El campo historial familiar es obligatorio.',
        'historial_familiar.string' => 'El campo historial familiar debe ser texto.',
        'historial_familiar.max' => 'El campo historial familiar no debe exceder los 400 caracteres.',

        'nombre_madre.string' => 'El nombre de la madre debe ser texto.',
        'nombre_madre.max' => 'El nombre de la madre no debe exceder los 250 caracteres.',
        'nombre_padre.string' => 'El nombre del padre debe ser texto.',
        'nombre_padre.max' => 'El nombre del padre no debe exceder los 250 caracteres.',
        'nombre_apoderado.string' => 'El nombre del apoderado debe ser texto.',
        'nombre_apoderado.max' => 'El nombre del apoderado no debe exceder los 250 caracteres.',
        'estado_apoderado.string' => 'El estado del apoderado debe ser texto.',
        'estado_apoderado.max' => 'El estado del apoderado no debe exceder los 250 caracteres.',
    ];
}
}
