<?php

namespace App\Http\Controllers\Estadisticas;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Psicologo;
use App\Models\Paciente;
use App\Traits\HttpResponseHelper;

class EstadisticasController extends Controller
{
    public function statistics(): JsonResponse
    {
        // Datos de ejemplo
        $data = [
            'usuarios' => 120,
            'pacientes' => 80,
            'psicologos' => 10,
            'citas' => 200,
        ];

        return response()->json($data);
    }

    public function porcentajePacientesPorGenero(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse('No se tiene acceso como psicólogo.')
                    ->send();
            }

            $pacientes = Paciente::where('idPsicologo', $psicologo->idPsicologo)->get();
            $total = $pacientes->count();

            $estadisticas = $pacientes
                ->groupBy(function ($paciente) {
                    return $paciente->genero ?: 'Desconocido';
                })
                ->map(function ($pacientes) use ($total) {
                    $count = $pacientes->count();
                    $porcentaje = $total > 0 ? round(($count / $total) * 100, 2) : 0;
                    return [
                        'cantidad' => $count,
                        'porcentaje' => $porcentaje
                    ];
                });

            return HttpResponseHelper::make()
                ->successfulResponse('Porcentaje de pacientes por género obtenido correctamente', $estadisticas)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }
}
