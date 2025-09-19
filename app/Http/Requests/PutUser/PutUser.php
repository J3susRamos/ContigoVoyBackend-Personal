<?php

namespace App\Http\Requests\PutUser;

use App\Models\Psicologo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PutUser extends FormRequest
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
        if ($psicologo = Psicologo::find($this->route('id'))) {
            $this->merge(['user_id' => $psicologo->user_id]);
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
            'name' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'email' => [
                'sometimes',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($this->route('id')), // Ignora el email actual del usuario
            ],
            'password' => 'sometimes|string|min:8|max:100',
            'fecha_nacimiento' => 'sometimes|date_format:d/m/Y',
            'imagen' => 'sometimes|string|nullable',
        ];
    }
}