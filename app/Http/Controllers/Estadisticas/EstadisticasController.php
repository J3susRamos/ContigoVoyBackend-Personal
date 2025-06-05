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
    public function porcentajePacientesPorGenero()
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
            if ($total === 0) {
                return HttpResponseHelper::make()
                    ->successfulResponse('No hay pacientes registrados.', [])
                    ->send();
            }

            $estadisticas = $pacientes->groupBy('genero')->map(function ($items) use ($total) {
                return [
                    'cantidad' => $items->count(),
                    'porcentaje' => round(($items->count() / $total) * 100)
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
