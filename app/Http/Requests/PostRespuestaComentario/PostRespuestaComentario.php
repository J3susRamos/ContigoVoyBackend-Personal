<?php

namespace App\Http\Requests\PostRespuestaComentario;

use Illuminate\Foundation\Http\FormRequest;

class PostRespuestaComentario extends FormRequest
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
            'idComentario' => 'required|exists:comentarios,idComentario',
            'respuesta' => 'required|string|max:1000',
            'nombre' => 'nullable|string|max:255',
        ];
    }
}
