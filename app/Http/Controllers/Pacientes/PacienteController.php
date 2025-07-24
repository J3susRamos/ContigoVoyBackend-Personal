<?php

namespace App\Http\Controllers\Pacientes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostPaciente\PostPaciente;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Psicologo;
use App\Traits\HttpResponseHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PacienteController extends Controller
{

    public function createPaciente(PostPaciente $requestPaciente, $idCita = null)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where("user_id", $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse("Solo los psicólogos pueden crear pacientes")
                    ->send();
            }

            $pacienteData = $requestPaciente->all();
            $pacienteData["apellido"] = trim(
                $pacienteData["apellidoPaterno"] . " " . $pacienteData["apellidoMaterno"]
            );
            unset($pacienteData["apellidoPaterno"], $pacienteData["apellidoMaterno"]);
            $pacienteData["fecha_nacimiento"] = Carbon::createFromFormat(
                "d / m / Y",
                $pacienteData["fecha_nacimiento"]
            )->format("Y-m-d");
            $pacienteData["idPsicologo"] = $psicologo->idPsicologo;
            $pacienteData["codigo"] = Paciente::generatePacienteCode();

            $paciente = Paciente::create($pacienteData);

            // Si viene el idCita, actualiza las citas
            if ($idCita) {
                Cita::where('idCita', $idCita)
                    ->update(['idPaciente' => $paciente->idPaciente]);
            }

            return HttpResponseHelper::make()
                ->successfulResponse("Paciente creado correctamente")
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrio un problema al procesar la solicitud." . $e->getMessage()
                )
                ->send();
        }
    }

    // Conteo de citas por paciente
    public function getCitasPaciente(int $idPaciente)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where("user_id", $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse("No se tiene acceso como psicólogo.")
                    ->send();
            }

            $paciente = Paciente::where("idPaciente", $idPaciente)
                ->where("idPsicologo", $psicologo->idPsicologo)
                ->first();

            if (!$paciente) {
                return HttpResponseHelper::make()
                    ->notFoundResponse(
                        "El paciente no pertenece al psicólogo autenticado."
                    )
                    ->send();
            }

            // Contar las citas del paciente por estado
            $citasPendientes = Cita::where("idPaciente", $idPaciente)
                ->where("estado_Cita", "Pendiente")
                ->count();

            $citasCanceladas = Cita::where("idPaciente", $idPaciente)
                ->where("estado_Cita", "Cancelada")
                ->count();

            $citasConfirmadas = Cita::where("idPaciente", $idPaciente)
                ->where("estado_Cita", "Confirmada")
                ->count();

            $response = [
                "pendientes" => $citasPendientes,
                "canceladas" => $citasCanceladas,
                "confirmadas" => $citasConfirmadas,
            ];

            return HttpResponseHelper::make()
                ->successfulResponse(
                    "Conteo de citas del paciente obtenido correctamente",
                    $response
                )
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud. " .
                        $e->getMessage()
                )
                ->send();
        }
    }

    public function updatePaciente(PostPaciente $requestPaciente, int $id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $pacienteData = $requestPaciente->all();
            $pacienteData["fecha_nacimiento"] = Carbon::createFromFormat(
                "d / m / Y",
                $pacienteData["fecha_nacimiento"]
            )->format("Y-m-d");
            $paciente->update($pacienteData);

            return HttpResponseHelper::make()
                ->successfulResponse("Paciente actualizado correctamente")
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrio un problema al procesar la solicitud." .
                        $e->getMessage()
                )
                ->send();
        }
    }

    public function showPacientesByPsicologo(Request $request)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where("user_id", $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse("No se tiene acceso como psicólogo")
                    ->send();
            }

            $shouldPaginate = $request->query("paginate", false);
            $perPage = $request->query("per_page", 10);

            $query = Paciente::where(
                "idPsicologo",
                $psicologo->idPsicologo
            )->with([
                "citas" => function ($query) {
                    $query
                        ->orderBy("fecha_cita", "desc")
                        ->orderBy("hora_cita", "desc")
                        ->limit(1);
                },
            ]);

            // Filtrar por género
            if ($request->filled("genero")) {
                $generos = explode(",", $request->query("genero"));
                $query->whereIn("genero", $generos);
            }

            // Filtrar por edad (espera "10 - 20,30 - 40")
            if ($request->filled("edad")) {
                $rangosEdad = explode(",", $request->query("edad"));
                $query->where(function ($q) use ($rangosEdad) {
                    foreach ($rangosEdad as $rango) {
                        [$min, $max] = array_map(
                            "intval",
                            explode(" - ", $rango)
                        );
                        $q->orWhereBetween("fecha_nacimiento", [
                            now()->subYears($max)->startOfDay(),
                            now()->subYears($min)->endOfDay(),
                        ]);
                    }
                });
            }

            // Filtrar por fecha de la última cita
            if (
                $request->filled("fecha_inicio") &&
                $request->filled("fecha_fin")
            ) {
                $fechaInicio = $request->query("fecha_inicio");
                $fechaFin = $request->query("fecha_fin");

                $query->whereHas("citas", function ($q) use (
                    $fechaInicio,
                    $fechaFin
                ) {
                    $q->whereBetween("fecha_cita", [$fechaInicio, $fechaFin]);
                });
            }

            // Filtrar por nombre
            if ($request->filled("nombre")) {
                $nombre = $request->query("nombre");
                $query->where(function ($q) use ($nombre) {
                    $q->where("nombre", "like", "%{$nombre}%")->orWhere(
                        "apellido",
                        "like",
                        "%{$nombre}%"
                    );
                });
            }

            if ($shouldPaginate) {
                $pacientesPaginator = $query->paginate($perPage);
                $data = $pacientesPaginator
                    ->getCollection()
                    ->map(function ($paciente) {
                        return $this->mapPaciente($paciente);
                    });

                return HttpResponseHelper::make()
                    ->successfulResponse("Pacientes obtenidos correctamente", [
                        "data" => $data,
                        "pagination" => [
                            "current_page" => $pacientesPaginator->currentPage(),
                            "last_page" => $pacientesPaginator->lastPage(),
                            "per_page" => $pacientesPaginator->perPage(),
                            "total" => $pacientesPaginator->total(),
                        ],
                    ])
                    ->send();
            } else {
                $pacientes = $query->get();
                $data = $pacientes->map(function ($paciente) {
                    return $this->mapPaciente($paciente);
                });

                return HttpResponseHelper::make()
                    ->successfulResponse(
                        "Pacientes obtenidos correctamente",
                        $data
                    )
                    ->send();
            }
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud. " .
                        $e->getMessage()
                )
                ->send();
        }
    }

    private function mapPaciente($paciente)
    {
        $ultimaCita = $paciente->citas->first();
        return [
            "idPaciente" => $paciente->idPaciente,
            "codigo" => $paciente->codigo,
            "DNI" => $paciente->DNI,
            "nombre" => $paciente->nombre . " " . $paciente->apellido,
            "email" => $paciente->email,
            "celular" => $paciente->celular,
            "genero" => $paciente->genero,
            "fecha_nacimiento" => $paciente->fecha_nacimiento,
            "edad" => Carbon::parse($paciente->fecha_nacimiento)->age,
            "ultima_cita_fecha" => $ultimaCita
                ? $ultimaCita->fecha_cita . " " . $ultimaCita->hora_cita
                : null,
        ];
    }

    public function showPacienteById($id)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where("user_id", $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse("No se tiene acceso como psicólogo.")
                    ->send();
            }

            $paciente = Paciente::where("idPaciente", $id)
                ->where("idPsicologo", $psicologo->idPsicologo)
                ->first();

            return HttpResponseHelper::make()
                ->successfulResponse(
                    "Paciente obtenido correctamente",
                    $paciente->toArray()
                )
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud. " .
                        $e->getMessage()
                )
                ->send();
        }
    }

    public function destroyPaciente(int $id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->delete();

            return HttpResponseHelper::make()
                ->successfulResponse("Paciente eliminado correctamente")
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Error al eliminar el blog: " . $e->getMessage()
                )
                ->send();
        }
    }

    public function getPacientesGenero()
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where("user_id", $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse("No se tiene acceso como psicólogo.")
                    ->send();
            }

            // Obtener el conteo de pacientes por género
            $pacientes = Paciente::where(
                "idPsicologo",
                $psicologo->idPsicologo
            )->get();
            $total = $pacientes->count();
            if ($total === 0) {
                return HttpResponseHelper::make()
                    ->successfulResponse("No hay pacientes registrados.", [])
                    ->send();
            }
            $estadisticas = $pacientes
                ->groupBy(function ($paciente) {
                    return $paciente->genero ?: "Desconocido"; // Agrupar por género, o 'Desconocido' si no se especifica
                })
                ->map(function ($items) use ($total) {
                    return [
                        "cantidad" => $items->count(),
                        "porcentaje" => round(($items->count() / $total) * 100),
                    ];
                });

            return HttpResponseHelper::make()
                ->successfulResponse(
                    "Porcentaje de pacientes por genero obtenido correctamente",
                    $estadisticas
                )
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud. " .
                        $e->getMessage()
                )
                ->send();
        }
    }

    public function getPacientesEdad()
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where("user_id", $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse("No se tiene acceso como psicólogo.")
                    ->send();
            }

            // Obtener el conteo de pacientes por rango de edad
            $estadisticas = Paciente::where(
                "idPsicologo",
                $psicologo->idPsicologo
            )
                ->get()
                ->groupBy(function ($paciente) {
                    $edad = Carbon::parse($paciente->fecha_nacimiento)->age;
                    if ($edad <= 12) {
                        return "0-12";
                    } elseif ($edad <= 17) {
                        return "13-17";
                    } elseif ($edad <= 24) {
                        return "18-24";
                    } elseif ($edad <= 34) {
                        return "25-34";
                    } elseif ($edad <= 44) {
                        return "35-44";
                    } else {
                        return "45+";
                    }
                })
                ->map(function ($pacientes) {
                    return $pacientes->count();
                });

            return HttpResponseHelper::make()
                ->successfulResponse(
                    "Estadísticas de pacientes por rango de edad obtenidas correctamente",
                    $estadisticas
                )
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud. " .
                        $e->getMessage()
                )
                ->send();
        }
    }

    public function getPacientesLugar()
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where("user_id", $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse("No se tiene acceso como psicólogo.")
                    ->send();
            }

            $estadisticas = Paciente::where(
                "idPsicologo",
                $psicologo->idPsicologo
            )
                ->whereNotNull("pais")
                ->select("pais", DB::raw("count(*) as total"))
                ->groupBy("pais")
                ->pluck("total", "pais");

            return HttpResponseHelper::make()
                ->successfulResponse(
                    "Estadísticas de pacientes por lugar obtenidas correctamente",
                    $estadisticas
                )
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud. " .
                        $e->getMessage()
                )
                ->send();
        }
    }
}
