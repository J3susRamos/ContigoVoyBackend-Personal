<?php

namespace App\Http\Controllers\Disponibilidad;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\HttpResponseHelper;
use Illuminate\Http\Request;
use App\Models\Psicologo;
use App\Models\Disponibilidad;
use Exception;

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

            $request->validate([ //esto
                'fecha' => 'required|date|after_or_equal:today',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'turno' => 'nullable|string', // a  esto
            ], [
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

    public function listarPsicologo()
    {
        try {

            $Auth = Auth::id();
            $psicologo = Psicologo::where('user_id', $Auth)->first();

            if (!$psicologo) {
                return response()->json(['message' => 'Psicologo no encontrado.'], 404);
            }

            $disponibilidad = Disponibilidad::where('idPsicologo', $psicologo->idPsicologo)
                ->get();

            return response()->json([
                'message' => 'Disponibilidad del psicologo',
                'data' => $disponibilidad,
            ], 201);
        } catch (Exception $e) {

            return HttpResponseHelper::make()
                ->internalErrorResponse("Ocurrió un problema al procesar la solicitud. " . $e->getMessage())
                ->send();
        }
    }

    public function listar(Request $request)
    {
        try {

            $Auth = Auth::id();

            if (!$Auth) {
                return response()->json(['message' => 'No esta autorizado'], 404);
            }

            $request->validate([
                'idPsicologo' => 'required|integer|exists:psicologos,idPsicologo',
            ]);

            $disponibilidad = Disponibilidad::where('idPsicologo', $request->idPsicologo)
                ->get();

            return response()->json([
                'message' => 'Disponibilidad del psicólogo',
                'data' => $disponibilidad,
            ], 200);
            
        } catch (Exception $e) {

            return HttpResponseHelper::make()
                ->internalErrorResponse("Ocurrió un problema al procesar la solicitud. " . $e->getMessage())
                ->send();
        }
    }

    public function editarDisponibilidad(Request $request)
    {
        try {

            $Auth = Auth::id();
            $psicologo = Psicologo::where('user_id', $Auth)->first();

            if (!$psicologo) {
                return response()->json(['message' => 'Psicologo no encontrado.'], 404);
            }

            $request->validate([
                'idDisponibilidad' => 'required|integer|exists:disponibilidad,idDisponibilidad',
                'fecha' => 'required|date|after_or_equal:today',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'turno' => 'nullable|string',
            ], [
                'fecha.after_or_equal' => 'La fecha seleccionada debe ser hoy o una fecha futura.',
                'hora_fin.after' => 'La hora final debe ser posterior a la hora de inicio.',
            ]);

            $disponibilidad = Disponibilidad::where('idDisponibilidad', $request->idDisponibilidad)
                ->where('idPsicologo', $psicologo->idPsicologo)
                ->first();

            if (!$disponibilidad) {
                return response()->json(['message' => 'Disponibilidad no encontrada o no pertenece al psicólogo'], 404);
            }

            $yaExiste = Disponibilidad::where('idPsicologo', $psicologo->idPsicologo)
                ->where('fecha', $request->fecha)
                ->where('idDisponibilidad', '!=', $request->id)
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

            $disponibilidad->update([
                'fecha' => $request->fecha,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'turno' => $request->turno,
            ]);

            return response()->json([
                'message' => 'Disponibilidad actualizada correctamente',
                'data' => $disponibilidad,
            ], 200);
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Ocurrió un problema al procesar la solicitud. " . $e->getMessage())
                ->send();
        }
    }

    public function eliminarDisponibilidad(Request $request)
    {
        try {

            $Auth = Auth::id();
            $psicologo = Psicologo::where('user_id', $Auth)->first();

            if (!$psicologo) {
                return response()->json(['message' => 'Psicologo no encontrado'], 404);
            }

            $request->validate([
                'idDisponibilidad' => 'required|integer|exists:disponibilidad,idDisponibilidad',
            ]);

            $disponibilidad = Disponibilidad::where('idDisponibilidad', $request->idDisponibilidad)
                ->where('idPsicologo', $psicologo->idPsicologo)
                ->first();

            if (!$disponibilidad) {
                return response()->json(['message' => 'Disponibilidad no encontrada o no pertenece al psicólogo'], 404);
            }

            $disponibilidad->delete();

            return response()->json([
                'message' => 'Disponibilidad eliminada correctamente'
            ], 200);
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Ocurrió un problema al procesar la solicitud. " . $e->getMessage())
                ->send();
        }
    }


//METODO ULTIMOS 7 DIAS
public function ultimos7dias(Request $request)
{
    try {

        $authUserId = Auth::id();

        $psicologo = Psicologo::where('user_id', $authUserId)->first();

        if (!$psicologo) {
            return response()->json([
                'message' => 'Psicólogo no encontrado para el usuario autenticado',
            ], 404);
        }

        // Calcular fecha de hace 7 días y fecha actual
        $fechaInicio = now()->subDays(7)->format('Y-m-d');
        $fechaFin = now()->format('Y-m-d');

        $disponibilidad = Disponibilidad::where('idPsicologo', $psicologo->idPsicologo)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return response()->json([
            'message' => 'Disponibilidad de los últimos 7 días',
            'data' => $disponibilidad,
        ], 200);

    } catch (Exception $e) {
        return HttpResponseHelper::make()
            ->internalErrorResponse("Error: " . $e->getMessage())
            ->send();
    }
}

}