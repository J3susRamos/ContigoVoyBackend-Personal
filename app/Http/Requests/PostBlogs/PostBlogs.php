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
            'slug'          => 'sometimes|string|max:100|unique:blogs,slug,' . $this->route('id'),
            'contenido'     => 'required|string|min:200',
            'imagenes'      => 'required|array',
            'imagenes.*'    => 'string',
            'idCategoria'   => 'required|integer|exists:categorias,idCategoria',
        ];
    }



        //mensajes oara validacion

    public function messages(): array
    {
        return [
            'idCategoria.required' => 'El campo categoría es obligatorio.',
            'idCategoria.integer' => 'La categoría debe ser un número válido.',
            'idCategoria.exists' => 'La categoría seleccionada no existe.',
            'tema.required' => 'El título es obligatorio.',
            'tema.min' => 'El título debe tener al menos 20 caracteres.',
            'tema.max' => 'El título no puede exceder 200 caracteres.',
            'slug.string' => 'El slug debe ser texto.',
            'slug.max' => 'El slug no puede exceder 100 caracteres.',
            'slug.unique' => 'Este slug ya está en uso.',
            'contenido.required' => 'El contenido es obligatorio.',
            'contenido.min' => 'El contenido debe tener al menos 200 caracteres.',
            'imagenes.required' => 'Debe agregar al menos una imagen.',
            'imagenes.array' => 'Las imágenes deben estar en formato de lista.',
            'imagenes.*.string' => 'Cada imagen debe ser una cadena de texto válida.',
        ];
    }
}
