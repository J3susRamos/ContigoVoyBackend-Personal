<?php

namespace App\Http\Controllers\Citas;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Traits\HttpResponseHelper;
use Illuminate\Http\Request;
use App\Http\Requests\PostCita\PostCita;
use App\Models\Paciente;
use App\Models\Psicologo;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\Boucher;

class CitaController extends Controller
{

    public function createCita(PostCita $request)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            $data = $request->validated();
            $data['idPsicologo'] = $psicologo->idPsicologo;

            if (!isset($data['estado_Cita']) || $data['estado_Cita'] !== 'Sin pagar') {
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'Bad Request',
                    'description' => 'El estado de la cita debe ser "Sin pagar".',
                    'result' => null,
                    'errorBag' => ['estado_Cita' => ['El estado de la cita debe ser "Sin pagar".']],
                ], 400);
            }

            $cita = Cita::create($data);

            return HttpResponseHelper::make()
                ->successfulResponse('Cita creada correctamente', ['data' => $cita])
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al crear la cita: ' . $e->getMessage())
                ->send();
        }
    }

    public function listunpaid(Request $request)
    {
        try {

            $citas = Cita::where('estado_Cita', 'Sin pagar')->get();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'OK',
                'description' => 'Lista de citas sin pagar obtenida correctamente.',
                'result' => $citas,
                'errorBag' => []
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Internal Server Error',
                'description' => 'Error al obtener la lista de citas sin pagar: ' . $e->getMessage(),
                'result' => null,
                'errorBag' => []
            ], 500);
        }
    }

    public function aceptarBoucher(Request $request)
    {
        try {

            $userId = Auth::id();

            $codigo = $request->input('codigo');

            if (!$codigo) {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Internal serve',
                    'description' => 'Error al recibir el codigo del boucher',
                ], 500);
            }

            $boucher = Boucher::where('codigo', $codigo)
                ->where('estado', 'pendiente')
                ->first();

            if (!$boucher) {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Internal server',
                    'description' => 'El estado del bouchcer debe ser pendiente',
                    'message' => null
                ], 500);
            }

            $boucher->estado = 'aceptado';
            $boucher->save();

            $idCita = $request->input('idCita');

            if (!$idCita) {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Internal serve',
                    'description' => 'Error al recibir la cita',
                ], 500);
            }

            $cita = Cita::where('idCita', $idCita)
                ->where('estado_Cita', 'Sin pagar')
                ->first();

            if (!$cita) {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Internal server',
                    'description' => 'Error en el estado de la cita'
                ], 500);
            }

            $cita->estado_Cita = 'Pendiente';
            $cita->save();

            $roomName = 'consulta_' . uniqid();
            $jitsiUrl = "https://meet.jit.si/{$roomName}";
            $cita->jitsi_url = $jitsiUrl;
            $cita->save();

            $result = [
                'idCita' => $cita->idCita,
                'estado_Cita' => $cita->estado_Cita,
                'jitsi_url' => $cita->jitsi_url,
            ];

            return response()->json([
                'status_code' => 200,
                'status_message' => 'OK',
                'description' => 'Cita y boucher habilitada correctamente.',
                'result' => $result,
                'errorBag' => []
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al aceptar el boucher.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function citaRealizada(Request $request)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Not Found',
                    'description' => 'Psicólogo no encontrado.',
                    'result' => null,
                    'errorBag' => []
                ], 404);
            }

            $idCita = $request->input('idCita');

            if (!$idCita) {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Internal serve',
                    'description' => 'Error al recibir la cita',
                ], 500);
            }

            $cita = Cita::where('idCita', $idCita)
                ->where('estado_Cita', 'Pendiente')
                ->first();

            if (!$cita) {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Internal serve',
                    'description' => 'Error al recibir la cita',
                ], 500);
            }

            $cita->estado_Cita = 'Realizado';
            $cita->jitsi_url = Null;
            $cita->save();

            $result = [
                'estado_Cita' => $cita->estado_Cita,
            ];

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Estado cambiado correctamente',
                'description' => 'Videollamada eliminada corrrectamente',
                'result' => $result
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Internal Server Error',
                'description' => 'Error al marcar la cita como realizada: ' . $e->getMessage(),
                'result' => null,
                'errorBag' => []
            ], 500);
        }
    }

    public function listarCitasPaciente()
    {
        try {
            $userId = Auth::id();

            // Buscar paciente por el user_id
            $paciente = Paciente::where('user_id', $userId)->first();

            if (!$paciente) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Not Found',
                    'description' => 'Paciente no encontrado.',
                    'result' => null,
                    'errorBag' => []
                ], 404);
            }

            // Buscar citas del paciente (solo pendientes y con jitsi_url no nulo)
            $citas = Cita::where('idPaciente', $paciente->idPaciente)
                ->where('estado_Cita', 'Pendiente')
                ->whereNotNull('jitsi_url')
                ->select('idCita', 'fecha_cita', 'hora_cita', 'jitsi_url', 'estado_Cita')
                ->get();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'OK',
                'description' => 'Citas del paciente obtenidas correctamente.',
                'result' => $citas,
                'errorBag' => []
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Internal Server Error',
                'description' => 'Error al listar las citas del paciente.',
                'result' => null,
                'errorBag' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }

    public function showAllCitasByPsicologo(Request $request)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un psicólogo asociado a este usuario.')
                    ->send();
            }

            $shouldPaginate = $request->query('paginate', false);
            $perPage = $request->query('per_page', 10);

            $query = Cita::where('idPsicologo', $psicologo->idPsicologo)
                ->with([
                    'paciente:idPaciente,nombre,apellido,codigo,genero,fecha_nacimiento',
                    'prepaciente:idPrePaciente,nombre'
                ])
                ->orderBy('fecha_cita', 'desc')
                ->orderBy('hora_cita', 'desc');

            // FILTROS
            if ($request->filled('genero')) {
                $generos = explode(',', $request->query('genero'));
                $query->whereHas('paciente', function ($q) use ($generos) {
                    $q->whereIn('genero', $generos);
                });
            }

            if ($request->filled('estado')) {
                $estados = explode(',', $request->query('estado'));
                $query->whereIn('estado_Cita', $estados);
            }

            if ($request->filled('edad')) {
                $rangos = explode(',', $request->query('edad'));
                $query->whereHas('paciente', function ($q) use ($rangos) {
                    $q->where(function ($subQuery) use ($rangos) {
                        foreach ($rangos as $rango) {
                            [$min, $max] = explode(' - ', $rango);
                            $minDate = Carbon::now()->subYears($max)->startOfDay();
                            $maxDate = Carbon::now()->subYears($min)->endOfDay();
                            $subQuery->orWhereBetween('fecha_nacimiento', [$minDate, $maxDate]);
                        }
                    });
                });
            }

            if ($request->filled('codigo')) {
                $codigo = $request->query('codigo');

                $query->where(function ($q) use ($codigo) {
                    // Si el código parece de prepaciente (ajusta la condición según tu lógica)
                    if (str_starts_with($codigo, 'Pre')) {
                        $q->whereNotNull('idPrePaciente');
                    } else {
                        $q->whereNotNull('idPaciente');
                    }
                });
            }

            if ($request->filled('nombre')) {
                $nombre = $request->query('nombre');

                $query->where(function ($q) use ($nombre) {
                    $q->whereHas('paciente', function ($subQ) use ($nombre) {
                        $subQ->whereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%$nombre%"]);
                    })->orWhereHas('prepaciente', function ($subQ) use ($nombre) {
                        $subQ->where('nombre', 'like', "%$nombre%");
                    });
                });
            }

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $from = $request->query('fecha_inicio');
                $to = $request->query('fecha_fin');
                $query->whereBetween('fecha_cita', [$from, $to]);
            }

            // PAGINACIÓN
            if ($shouldPaginate) {
                $citasPaginator = $query->paginate($perPage);
                $data = $citasPaginator->getCollection()->map(function ($cita) {
                    return $this->mapCita($cita);
                });

                return HttpResponseHelper::make()
                    ->successfulResponse('Citas paginadas obtenidas correctamente', [
                        'data' => $data,
                        'pagination' => [
                            'current_page' => $citasPaginator->currentPage(),
                            'last_page' => $citasPaginator->lastPage(),
                            'per_page' => $citasPaginator->perPage(),
                            'total' => $citasPaginator->total()
                        ]
                    ])
                    ->send();
            } else {
                $citas = $query->get();
                $data = $citas->map(function ($cita) {
                    return $this->mapCita($cita);
                });

                return HttpResponseHelper::make()
                    ->successfulResponse('Lista de citas obtenida correctamente', $data)
                    ->send();
            }
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener las citas: ' . $e->getMessage())
                ->send();
        }
    }

    private function mapCita($cita)
    {
        return [
            'idCita' => $cita->idCita,
            'idPaciente' => $cita->idPaciente,
            'idPsicologo' => $cita->idPsicologo,
            'paciente' => $cita->paciente
                ? $cita->paciente->nombre . ' ' . $cita->paciente->apellido
                : ($cita->prepaciente ? $cita->prepaciente->nombre : null),
            'codigo' => optional($cita->paciente)->codigo,
            'genero' => optional($cita->paciente)->genero ?: null,
            'fecha_nacimiento' => optional($cita->paciente)->fecha_nacimiento,
            'motivo' => $cita->motivo_Consulta,
            'estado' => $cita->estado_Cita,
            'edad' => $cita->paciente && $cita->paciente->fecha_nacimiento
                ? Carbon::parse($cita->paciente->fecha_nacimiento)->age
                : null,
            'fecha_inicio' => "{$cita->fecha_cita} {$cita->hora_cita}",
            'duracion' => "{$cita->duracion} min."
        ];
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

    //Consulta para las citas del psicologo (total por fechas)
    public function getCitasPorPeriodoPsicologo()
    {
        $userId = Auth::id();
        $psicologo = Psicologo::where('user_id', $userId)->first();

        if (!$psicologo) {
            return HttpResponseHelper::make()
                ->notFoundResponse('No se encontró un psicólogo asociado a este usuario.')
                ->send();
        }

        $idPsicologo = $psicologo->idPsicologo;

        $citas = Cita::selectRaw('DATE(fecha_cita) as fecha, COUNT(*) as total')
            ->where('idPsicologo', $idPsicologo)
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

        //Cambio en total de pacientes
        $totalPacientes = Paciente::where('idPsicologo', $idPsicologo)
            ->whereNotNull('idPaciente')
            ->distinct('idPaciente')
            ->count('idPaciente');



        //Cambio en nuevos pacientes
        $nuevosPacientes = Cita::where('idPsicologo', $idPsicologo)
            ->where('estado_Cita', 'confirmada')
            ->whereNotNull('idPaciente')
            ->where('fecha_Cita', '>=', now()->subDays(7))
            ->orderBy('fecha_Cita', 'asc')
            ->count();

        return HttpResponseHelper::make()
            ->successfulResponse('Datos del dashboard cargados correctamente', [
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
