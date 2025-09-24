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
            'slug'          => 'sometimes|string|max:100|unique:posts,slug,' . $this->route('id'),
            'descripcion'   => 'required|string|min:200', // contenido -> descripcion
            'imagen'        => 'required|string', // imagenes -> imagen (string simple)
            'especialidad'  => 'required|string', // idCategoria -> especialidad
        ];
    }



        //mensajes oara validacion

    public function messages(): array
    {
        return [
            'especialidad.required' => 'El campo categoría es obligatorio.',
            'tema.required' => 'El título es obligatorio.',
            'tema.min' => 'El título debe tener al menos 20 caracteres.',
            'tema.max' => 'El título no puede exceder 200 caracteres.',
            'slug.string' => 'El slug debe ser texto.',
            'slug.max' => 'El slug no puede exceder 100 caracteres.',
            'slug.unique' => 'Este slug ya está en uso.',
            'descripcion.required' => 'El contenido es obligatorio.',
            'descripcion.min' => 'El contenido debe tener al menos 200 caracteres.',
            'imagen.required' => 'Debe agregar una imagen.',
            'imagen.string' => 'Formato de imagen inválido.',
        ];
    }
}
