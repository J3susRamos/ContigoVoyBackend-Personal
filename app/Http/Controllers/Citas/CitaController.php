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
            $citas = Cita::with([
                'paciente:idPaciente,nombre,apellido,celular',
                'psicologo' => function ($query) {
                    $query->select('idPsicologo', 'user_id')
                        ->with(['user:user_id,name,apellido']);
                },

                'bouchers' => function ($query) {
                    $query->select('idBoucher', 'idCita', 'codigo', 'estado', 'created_at', 'imagen')
                        ->where('estado', 'pendiente');
                }
            ])
                ->where('estado_Cita', 'Sin pagar')
                ->get();

            $result = $citas->map(function ($cita) {
                return [
                    'idCita' => $cita->idCita,
                    'fecha_cita' => $cita->fecha_cita,
                    'hora_cita' => $cita->hora_cita,
                    'estado_Cita' => $cita->estado_Cita,
                    'paciente' => [
                        'idPaciente' => $cita->paciente->idPaciente,
                        'nombre' => $cita->paciente->nombre,
                        'apellido' => $cita->paciente->apellido,
                        'celular' => str_replace(' ', '', $cita->paciente->celular),
                    ],
                    'motivo_Consulta' => $cita->motivo_Consulta,
                    'duracion' => $cita->duracion,

                    'psicologo' => [
                        'idPsicologo' => $cita->psicologo?->idPsicologo,
                        'nombre' => $cita->psicologo?->user?->name,
                        'apellido' => $cita->psicologo?->user?->apellido,
                    ],

                    'boucher' => $cita->bouchers->map(function ($boucher) {
                        return [
                            'idBoucher' => $boucher->idBoucher,
                            'codigo' => $boucher->codigo,
                            'estado' => $boucher->estado,
                            'fecha_creacion' => $boucher->created_at?->format('Y-m-d H:i:s'),
                            'imagen' => $boucher->imagen,
                        ];
                    })->first(),

                ];
            });

            return response()->json([
                'status_code' => 200,
                'status_message' => 'OK',
                'description' => 'Lista de citas sin pagar obtenida correctamente.',
                'result' => $result,
                'errorBag' => []
            ], 200);
        } catch (\Exception $e) {
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

    public function listarCitasPaciente(Request $request) // APLICADOS POR FILTRO
    {
        try {
            $userId = Auth::id();

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

            $estadoCita = $request->query('estado_Cita');
            $estadoBoucher = $request->query('estado_boucher');
            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');
            $nombrePsicologo = $request->query('nombre_psicologo');

            $citas = Boucher::with(['cita.psicologo.user'])
                ->when($estadoBoucher, function ($query) use ($estadoBoucher) {
                    $query->where('estado', $estadoBoucher);
                })
                ->whereHas('cita', function ($query) use ($paciente, $estadoCita, $fechaInicio, $fechaFin, $nombrePsicologo) {
                    $query->where('idPaciente', $paciente->idPaciente);

                    if ($estadoCita) {
                        $query->where('estado_Cita', $estadoCita);
                    }

                    if ($fechaInicio && $fechaFin) {
                        $query->whereBetween('fecha_cita', [$fechaInicio, $fechaFin]);
                    }

                    if ($nombrePsicologo) {
                        $query->whereHas('psicologo.user', function ($q) use ($nombrePsicologo) {
                            $q->where('name', 'like', '%' . $nombrePsicologo . '%');
                        });
                    }
                })
                ->paginate(10);

            return response()->json([
                'status_code' => 200,
                'status_message' => 'OK',
                'description' => 'Citas del paciente obtenidas correctamente.',
                'citas' => $citas,
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

            if ($request->filled('jitsi_url')) {
                $jitsi_url = explode(',', $request->query('jitsi_url'));
                $query->whereIn('jitsi_url', $jitsi_url);
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
            'duracion' => "{$cita->duracion} min.",
            'jitsi_url' => "{$cita->jitsi_url}"
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
            'sin_pagar' => Cita::where('estado_Cita', 'Sin pagar')->count(),
            'pendientes' => Cita::where('estado_Cita', 'Pendiente')->count(),
            'canceladas' => Cita::where('estado_Cita', 'Cancelada')->count(),
            'realizadas' => Cita::where('estado_Cita', 'Realizada')->count(),
            'ausentes' => Cita::where('estado_Cita', 'Ausente')->count(),
            'reprogramadas' => Cita::where('estado_Cita', 'Reprogramada')->count()
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
        $citasSinPagar = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'Sin pagar')->count();
        $citasRealizadas = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'Realizada')->count();
        $citasPendientes = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'Pendiente')->count();
        $citasCanceladas = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'Cancelada')->count();
        $citasReprogramadas = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'Reprogramada')->count();
        $citasAusentes = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'Ausente')->count();
        $totalMinutosReservados = Cita::where('idPsicologo', $idPsicologo)
            ->whereIn('estado_Cita', ['Pendiente'])
            ->sum('duracion');

        //Cambio en total de pacientes
        $totalPacientes = Paciente::where('idPsicologo', $idPsicologo)
            ->whereNotNull('idPaciente')
            ->distinct('idPaciente')
            ->count('idPaciente');



        //Cambio en nuevos pacientes
        $nuevosPacientes = Cita::where('idPsicologo', $idPsicologo)
            ->where('estado_Cita', 'Pendiente')
            ->whereNotNull('idPaciente')
            ->where('fecha_Cita', '>=', now()->subDays(7))
            ->orderBy('fecha_Cita', 'asc')
            ->count();

        return HttpResponseHelper::make()
            ->successfulResponse('Datos del dashboard cargados correctamente', [
                'total_citas' => $totalCitas,
                'citas_sin_pagar' => $citasSinPagar,
                'citas_realizadas' => $citasRealizadas,
                'citas_pendientes' => $citasPendientes,
                'citas_ausentes' => $citasAusentes,
                'citas_reprogramadas' => $citasReprogramadas,
                'citas_canceladas' => $citasCanceladas,
                'total_minutos_reservados' => $totalMinutosReservados,
                'total_pacientes' => $totalPacientes,
                'nuevos_pacientes' => $nuevosPacientes
            ])
            ->send();
    }
}
