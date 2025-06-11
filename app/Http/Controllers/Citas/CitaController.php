<?php

namespace App\Http\Controllers\Citas;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Traits\HttpResponseHelper;
use Illuminate\Http\Request;
use App\Http\Requests\PostCita\PostCita;
use App\Models\Paciente;
use App\Models\Psicologo;
use Exception;
use Illuminate\Support\Facades\Auth;

class CitaController extends Controller
{

    public function createCita(PostCita $request)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            $data = $request->validated();
            $data['idPsicologo'] = $psicologo->idPsicologo;

            $cita = Cita::create($data);

            return HttpResponseHelper::make()
                ->successfulResponse('Cita creada correctamente')
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al crear la cita: ' . $e->getMessage())
                ->send();
        }
    }

    public function showAllCitasByPsicologo()
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un psicólogo asociado a este usuario.')
                    ->send();
            }

            $id = $psicologo->idPsicologo;
            $citas = Cita::where('idPsicologo', $id)
                ->with([
                    'paciente:idPaciente,nombre,apellido,codigo',
                    'prepaciente:idPrePaciente,nombre'
                ])
                ->get()
                ->map(function ($cita) {
                    return [
                        'idCita' => $cita->idCita,
                        'idPaciente' => $cita->idPaciente,
                        'idPsicologo' => $cita->idPsicologo,
                        'paciente' => $cita->paciente
                            ? $cita->paciente->nombre . ' ' . $cita->paciente->apellido
                            : ($cita->prepaciente ? $cita->prepaciente->nombre : null),
                        'codigo' => optional($cita->paciente)->codigo,
                        'motivo' => $cita->motivo_Consulta,
                        'estado' => $cita->estado_Cita,
                        'fecha_inicio' => "{$cita->fecha_cita} {$cita->hora_cita}",
                        'duracion' => "{$cita->duracion} min."
                    ];
                });

            return HttpResponseHelper::make()
                ->successfulResponse('Lista de citas obtenida correctamente', $citas)
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener las citas: ' . $e->getMessage())
                ->send();
        }
    }

    public function showCitaById(int $id)
    {
        try {
            $cita = Cita::with([
                'etiqueta:idEtiqueta,nombre',
                'tipoCita:idTipoCita,nombre',
                'canal:idCanal,nombre',
                'paciente:idPaciente,nombre,apellido',
                'prepaciente:idPrePaciente,nombre,apellido',
                'psicologo'
            ])->find($id);

            if (!$cita) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('Cita no encontrada')
                    ->send();
            }

            $response = [
                'idCita' => $cita->idCita,
                'idPaciente' => $cita->idPaciente,
                'idPsicologo' => $cita->idPsicologo,
                'paciente' => $cita->paciente
                    ? $cita->paciente->nombre . ' ' . $cita->paciente->apellido
                    : ($cita->prepaciente ? $cita->prepaciente->nombre : null),
                'motivo' => $cita->motivo_Consulta,
                'estado' => $cita->estado_Cita,
                'fecha' => $cita->fecha_cita,
                'hora' => $cita->hora_cita,
                'duracion' => $cita->duracion . ' min.',
                'tipo' => optional($cita->tipoCita)->nombre,
                'canal' => optional($cita->canal)->nombre,
                'etiqueta' => optional($cita->etiqueta)->nombre,
                'color' => $cita->colores,
            ];

            return HttpResponseHelper::make()
                ->successfulResponse('Cita obtenida correctamente', $response)
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener la cita: ' . $e->getMessage())
                ->send();
        }
    }

    public function showCitasPendientes(int $id)
    {
        try {
            $citas = Cita::where('estado_Cita', 'Pendiente')
                ->where('idPsicologo', $id)
                ->get()
                ->map(function ($cita) {
                    return [
                        'fecha' => $cita->fecha_cita,
                        'hora'  => substr($cita->hora_cita, 0, 5),
                    ];
                });

            return HttpResponseHelper::make()
                ->successfulResponse('Lista de citas obtenida correctamente', $citas)
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener las citas: ' . $e->getMessage())
                ->send();
        }
    }

    public function updateCita(PostCita $request, int $id)
    {
        try {
            $cita = Cita::findOrFail($id);
            $cita->update($request->all());

            return HttpResponseHelper::make()
                ->successfulResponse('Cita actualizada correctamente')
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al actualizar la cita: ' . $e->getMessage())
                ->send();
        }
    }

    public function destroyCita(int $id)
    {
        try {
            $cita = Cita::findOrFail($id);
            $cita->delete();

            return HttpResponseHelper::make()
                ->successfulResponse('Cita eliminada correctamente')
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al eliminar la cita: ' . $e->getMessage())
                ->send();
        }
    }
  
    public function getCitasPorEstado()
    {
        $estadisticas = [
            'confirmada' => Cita::where('estado_Cita', 'Confirmada')->count(),
            'pendientes' => Cita::where('estado_Cita', 'Pendiente')->count(),
            'canceladas' => Cita::where('estado_Cita', 'Cancelada')->count(),
        ];

        return response()->json($estadisticas);
    }

    public function getCitasPorPeriodo()
    {
        $citas = Cita::selectRaw('DATE(fecha_cita) as fecha, COUNT(*) as total')
            ->groupBy('fecha_cita')
            ->orderBy('fecha_cita', 'asc')
            ->get();

        return response()->json($citas);
    }

    //Nueva consulta de dashboard del psicólogo
    
    public function psicologoDashboard()
    {
        $userId = Auth::id();
        $psicologo = Psicologo::where('user_id', $userId)->first();
    
        if (!$psicologo) {
            return HttpResponseHelper::make()
                ->notFoundResponse('No se encontró un psicólogo asociado a este usuario.')
                ->send();
        }
    
        $idPsicologo = $psicologo->idPsicologo;
    
        // Obtener citas del psicólogo
        $totalCitas = Cita::where('idPsicologo', $idPsicologo)->count();
        $citasCompletadas = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'completada')->count();
        $citasPendientes = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'pendiente')->count();
        $citasCanceladas = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'cancelada')->count();
            // citas añadidas a la consulta
        $citasConfirmadas = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'confirmada')->count();

        $totalMinutosReservados = Cita::where('idPsicologo', $idPsicologo)
        ->whereIn('estado_Cita', ['completada', 'pendiente'])  
        ->sum('duracion'); 
        
        $pacientesIds = Cita::where('idPsicologo', $idPsicologo)
        ->whereIn('estado_Cita', ['completada', 'pendiente'])
        ->pluck('idPaciente')
        ->unique();

        // Total de pacientes únicos
        $totalPacientes = $pacientesIds->count();


        $nuevosPacientes = Cita::where('idPsicologo', $idPsicologo)
        ->whereIn('estado_Cita', ['completada', 'pendiente'])
        ->whereNotNull('idPaciente')
        ->orderBy('fecha_Cita', 'asc')
        ->get()
        ->groupBy('idPaciente') 
        ->filter(function ($citasPaciente) {
            $primeraCita = $citasPaciente->first();
            return optional($primeraCita)->fecha_Cita >= now()->subDays(7);
        })
        ->count();
    
        return HttpResponseHelper::make()
            ->successfulResponse('Datos del dashboard cargados correctamente',[
            'total_citas' => $totalCitas,
            'citas_completadas' => $citasCompletadas,
            'citas_pendientes' => $citasPendientes,
            'citas_canceladas' => $citasCanceladas,
            'total_minutos_reservados' => $totalMinutosReservados,
            'total_pacientes' => $totalPacientes,
            'nuevos_pacientes' => $nuevosPacientes, 
            'citas_confirmadas' => $citasConfirmadas
            ])
            ->send();
    }
}

