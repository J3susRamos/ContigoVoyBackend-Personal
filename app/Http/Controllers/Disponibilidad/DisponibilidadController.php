<?php

namespace App\Http\Controllers\Disponibilidad;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\HttpResponseHelper;
use Illuminate\Http\Request;
use App\Models\Psicologo;
use App\Models\Disponibilidad;

class DisponibilidadController extends Controller
{
    public function crearDisponibilidad(Request $request)
    {

        try {

            $Auth = Auth::id();
            $psicologo = Psicologo::where("user_id", $Auth)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse("Solo los psicólogos pueden crear disponibilidad")
                    ->send();
            }

            $request->validate([
                'fecha' => 'required|date|after_or_equal:today',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'turno' => 'nullable|string',
            ],[
                'fecha.after_or_equal' => 'La fecha seleccionada debe ser hoy o una fecha futura.',
                'hora_fin.after' => 'La hora final debe ser posterior a la hora de inicio.',
            ]);

            $yaExiste = Disponibilidad::where('idPsicologo', $psicologo->idPsicologo)
                ->where('fecha', $request->fecha)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('hora_inicio', [$request->hora_inicio, $request->hora_fin])
                        ->orWhereBetween('hora_fin', [$request->hora_inicio, $request->hora_fin])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('hora_inicio', '<=', $request->hora_inicio)
                                ->where('hora_fin', '>=', $request->hora_fin);
                        });
                })
                ->exists();

            if ($yaExiste) {
                return HttpResponseHelper::make()
                    ->validationErrorResponse("Ya existe una disponibilidad en ese rango horario para este psicólogo.", [])
                    ->send();
            }


            $disponibilidad = Disponibilidad::create([
                'idPsicologo' => $psicologo->idPsicologo,
                'fecha' => $request->fecha,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'turno' => $request->turno,
            ]);

            return response()->json([
                'message' => 'Disponibilidad guardada correctamente',
                'data' => $disponibilidad,
            ], 201);
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Ocurrió un problema al procesar la solicitud. " . $e->getMessage())
                ->send();
        }
    }
}
