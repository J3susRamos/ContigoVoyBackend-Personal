<?php

namespace App\Http\Requests\PostBlogs;

use Illuminate\Foundation\Http\FormRequest;

class PostBlogs extends FormRequest
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
            'tema'          => 'required|string|min:20|max:200',
            'contenido'     => 'required|string|min:200',
            'imagenes'      => 'required|array|min:1|max:6',
            'imagenes.*'    => 'required|string', // Cada imagen debe ser un string (base64 o URL)
            'idCategoria'   => 'required|exists:categorias,idCategoria',
        ];
    }



    //mensajes oara validacion

    public function messages(): array
    {
        return [
            'idCategoria.required' => 'El campo categoría es obligatorio.',
            'idCategoria.exists' => 'La categoría seleccionada no existe.',
            'contenido.required' => 'El contenido es obligatorio.',
            'contenido.min' => 'El contenido debe tener al menos 200 caracteres.',
            'imagenes.required' => 'Debe agregar al menos 1 imagen.',
            'imagenes.min' => 'Debe agregar un mínimo de 1 imagen.',
            'imagenes.max' => 'No puede agregar más de 6 imágenes.',
            'imagenes.*.required' => 'Todas las imágenes son obligatorias.',
            'imagenes.*.string' => 'Formato de imagen inválido.',
            'idPsicologo.required' => 'Debe especificar un psicólogo.',
            'idPsicologo.exists' => 'El psicólogo seleccionado no existe.'
        ];
    }
}
