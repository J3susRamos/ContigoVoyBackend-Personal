<?php

namespace App\Http\Requests\PutPsicologo;

use Illuminate\Foundation\Http\FormRequest;

class PutPsicologo extends FormRequest
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
            'titulo' => 'sometimes|string|max:100',
            'introduccion' => 'sometimes|string|max:350',
            'pais' => 'sometimes|string|max:100',
            'genero' => 'sometimes|string|max:50',
            'experiencia' => 'sometimes|integer',
            'especialidades' => 'sometimes|array|min:1|max:3',
            'horario' => 'sometimes|array',
            'horario.*' => 'array',
            'horario.*.*' => 'array|size:2',

            'celular' => 'sometimes|nullable|string|min:9|max:20',
        ];
    }
}
