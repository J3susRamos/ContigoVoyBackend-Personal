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
    public function statistics()
    {
        try {
            $userId = Auth::id();
            
            // Verificar si el usuario es psicólogo
            $psicologo = Psicologo::where('user_id', $userId)->first();
            
            if ($psicologo) {
                // Usuario es PSICOLOGO - estadísticas de sus pacientes
                $pacientes = Paciente::where('idPsicologo', $psicologo->idPsicologo)->get();
            } else {
                // Usuario es ADMIN (u otro rol autorizado) - estadísticas de todos los pacientes
                $pacientes = Paciente::all();
            }

            // Lógica de estadísticas generales
            $total = $pacientes->count();
            $estadisticasGenero = $pacientes->groupBy('genero')->map(function ($items) use ($total) {
                return [
                    'cantidad' => $items->count(),
                    'porcentaje' => $total > 0 ? round(($items->count() / $total) * 100) : 0
                ];
            });

            $estadisticas = [
                'total_pacientes' => $total,
                'por_genero' => $estadisticasGenero,
            ];

            return HttpResponseHelper::make()
                ->successfulResponse('Estadísticas obtenidas correctamente', $estadisticas)
                ->send();
            
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function porcentajePacientesPorGenero()
    {
        try {
            $userId = Auth::id();
            
            // Verificar si el usuario es psicólogo
            $psicologo = Psicologo::where('user_id', $userId)->first();
            
            if ($psicologo) {
                // Usuario es PSICOLOGO - estadísticas de sus pacientes
                $pacientes = Paciente::where('idPsicologo', $psicologo->idPsicologo)->get();
            } else {
                // Usuario es ADMIN (u otro rol autorizado) - estadísticas de todos los pacientes
                $pacientes = Paciente::all();
            }

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