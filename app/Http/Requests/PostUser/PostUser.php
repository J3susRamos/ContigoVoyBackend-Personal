<?php

namespace App\Http\Requests\PostUser;

use App\Models\Psicologo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostUser extends FormRequest
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
            'name' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($this->input('user_id'), 'user_id'),
            ],
            'password' => 'required|string|min:8|max:100',
            'fecha_nacimiento' => 'required',
            'fecha_creacion' => 'date',
            'imagen' => 'nullable|string',
        ];
    }
}
