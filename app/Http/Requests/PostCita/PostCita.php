<?php
namespace App\Http\Requests\PostCita;

use App\Models\Cita;
use Illuminate\Foundation\Http\FormRequest;

class PostCita extends FormRequest
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
            'idEtiqueta' => 'sometimes|exists:etiquetas,idEtiqueta',
            'idTipoCita' => 'sometimes|exists:tipo_citas,idTipoCita',
            'idCanal' => 'sometimes|exists:canales,idCanal',
            'idPaciente' => 'sometimes|exists:pacientes,idPaciente',
            'idPrePaciente' => 'sometimes|exists:prepacientes,idPrePaciente',
            'idPsicologo' => 'sometimes|exists:psicologos,idPsicologo',
            'colores' => 'nullable|string',
            'fecha_cita' => 'required|date',
            'duracion' => 'required|integer',
            'hora_cita' => 'required|date_format:H:i:s',
            'motivo_Consulta' => 'nullable|string',
            'estado_Cita' => 'sometimes|in:Pendiente,Confirmada,Cancelada,Sin pagar,Reprogramada,Realizada',
            'fecha_limite' => 'required|date',
            'colores' => 'nullable|string',
            'duracion' => 'nullable|integer|min:0',
            'jitsi_url' => 'nullable|string',


            'fecha_cita' => [
                function ($attribute, $value, $fail) {
                    $exists = Cita::where('fecha_cita', $value)
                        ->where('hora_cita', request('hora_cita'))
                        ->exists();
    
                    if ($exists) {
                        $fail('Ya existe una cita programada en esta fecha y hora.');
                    }
                }
            ],
        ];
    }
}
