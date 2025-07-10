<?php

namespace App\Http\Requests\PostPaciente;

use App\Models\Paciente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostPaciente extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function prepareForValidation()
    {
        if ($paciente = Paciente::find($this->route("id"))) {
            $this->merge(["idPaciente" => $paciente->idPaciente]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "nombre" => "required|string|max:100",
            "apellidoPaterno" => "required|string|max:50",
            "apellidoMaterno" => "required|string|max:50",
            "email" => [
                "required",
                "email",
                "max:100",
                Rule::unique("pacientes", "email")->ignore(
                    $this->input("idPaciente"),
                    "idPaciente"
                ),
            ],
            "fecha_nacimiento" => "required",
            "ocupacion" => "required|string|max:100",
            "estadoCivil" => "required|string|max:100",
            "genero" => "required|string|max:20",
            "DNI" => "required|digits_between:8,15",
            "celular" => "required|string|min:3|max:30",
            "direccion" => "required|string|max:250",
            "pais" => "required|string|max:100",
            "departamento" => "required|string|max:100",
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no debe superar los 100 caracteres.',

            'apellidoPaterno.required' => 'El apellido paterno es obligatorio.',
            'apellidoPaterno.string' => 'El apellido paterno debe ser texto.',
            'apellidoPaterno.max' => 'El apellido paterno no debe superar los 50 caracteres.',

            'apellidoMaterno.required' => 'El apellido materno es obligatorio.',
            'apellidoMaterno.string' => 'El apellido materno debe ser texto.',
            'apellidoMaterno.max' => 'El apellido materno no debe superar los 50 caracteres.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ingresar un correo electrónico válido.',
            'email.max' => 'El correo electrónico no debe superar los 100 caracteres.',
            'email.unique' => 'El correo electrónico ya está registrado.',

            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',

            'ocupacion.required' => 'La ocupación es obligatoria.',
            'ocupacion.string' => 'La ocupación debe ser una cadena de texto.',
            'ocupacion.max' => 'La ocupación no debe superar los 100 caracteres.',

            'estadoCivil.required' => 'El estado civil es obligatorio.',
            'estadoCivil.string' => 'El estado civil debe ser una cadena de texto.',
            'estadoCivil.max' => 'El estado civil no debe superar los 100 caracteres.',

            'genero.required' => 'El género es obligatorio.',
            'genero.string' => 'El género debe ser una cadena de texto.',
            'genero.max' => 'El género no debe superar los 20 caracteres.',

            'DNI.required' => 'El DNI es obligatorio.',
            'DNI.digits_between' => 'El DNI debe contener solo números y tener entre 8 y 15 dígitos.',

            'celular.required' => 'El número de celular es obligatorio.',
            'celular.string' => 'El número de celular debe ser una cadena de texto.',
            'celular.min' => 'El número de celular debe tener al menos 3 caracteres.',
            'celular.max' => 'El número de celular no debe superar los 30 caracteres.',

            'direccion.required' => 'La dirección es obligatoria.',
            'direccion.string' => 'La dirección debe ser una cadena de texto.',
            'direccion.max' => 'La dirección no debe superar los 250 caracteres.',

            'pais.required' => 'El país es obligatorio.',
            'pais.string' => 'El país debe ser una cadena de texto.',
            'pais.max' => 'El país no debe superar los 100 caracteres.',

            'departamento.required' => 'El departamento es obligatorio.',
            'departamento.string' => 'El departamento debe ser una cadena de texto.',
            'departamento.max' => 'El departamento no debe superar los 100 caracteres.',
        ];
    }
}
