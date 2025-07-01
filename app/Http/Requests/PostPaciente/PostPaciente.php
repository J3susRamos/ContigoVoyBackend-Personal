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
            "apellido" => "required|string|max:100",
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
            "DNI" => "required|string",
            "celular" => "required|string|min:9|max:9",
            "direccion" => "required|string|max:250",
            "pais" => "required|string|max:100",
            // "provincia" => "required|string|max:100",
            "departamento" => "required|string|max:100",
        ];
    }
}
