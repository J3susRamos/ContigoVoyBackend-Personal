<?php

namespace App\Http\Controllers\Pacientes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostPaciente\PostPaciente;
use App\Http\Requests\PostUser\PostUser;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Psicologo;
use App\Traits\HttpResponseHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\CredencialesPacienteMail;
use Illuminate\Support\Facades\Cache;
use App\Mail\CodigoRecuperacion;
use App\Mail\NuevaContrasenaGenerada;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class PacienteController extends Controller
{

    public function createPaciente(PostPaciente $requestPaciente, $idCita = null)
    {
        try {
            $psicologoAuthId = Auth::id();
            $psicologo = Psicologo::where("user_id", $psicologoAuthId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse("Solo los psicólogos pueden crear pacientes")
                    ->unauthorizedResponse("Solo los psicólogos pueden crear pacientes")
                    ->send();
            }

            if (PACIENTE::where('DNI', $requestPaciente->DNI)->exists()) {
                return response()->json(['message' => "El DNI ya esta registrado"], 400);
            }

            if (PACIENTE::where('email', $requestPaciente->email)->exists()) {
                return response()->json(['message' => "El email ya esta registrado"], 400);
            }

            $data = $requestPaciente->validated();

            $randomPassword = Str::random(8);

            // datos del usuario
            $user = new User();
            $user->name = $data["nombre"];
            $user->apellido = trim($data["apellidoPaterno"] . " " . $data["apellidoMaterno"]);
            $user->email = $data["email"];
            $user->password = bcrypt($randomPassword);
            $user->fecha_nacimiento = Carbon::createFromFormat("d / m / Y", $data["fecha_nacimiento"])->format("Y-m-d");
            $user->rol = "PACIENTE";
            $user->save();

            // paciente con el user_id relacionado
            $paciente = new Paciente();
            $paciente->nombre = $data["nombre"];
            $paciente->apellido = $user->apellido;
            $paciente->email = $data["email"];
            $paciente->fecha_nacimiento = $user->fecha_nacimiento;
            $paciente->genero = $data["genero"];
            $paciente->ocupacion = $data["ocupacion"];
            $paciente->estadoCivil = $data["estadoCivil"];
            $paciente->DNI = $data["DNI"];
            $paciente->celular = $data["celular"];
            $paciente->direccion = $data["direccion"];
            $paciente->departamento = $data["departamento"];
            $paciente->imagen = $data["imagen"] ?? null;
            $paciente->pais = $data["pais"];
            $paciente->idPsicologo = $psicologo->idPsicologo;
            $paciente->codigo = Paciente::generatePacienteCode();
            $paciente->user_id = $user->user_id;
            $paciente->save();

            $user->assignRole('PACIENTE');

            Mail::to($user->email)->send(new CredencialesPacienteMail(
                $user->name,
                $user->email,
                $randomPassword
            ));

            return HttpResponseHelper::make()
                ->successfulResponse("Paciente creado correctamente")
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Ocurrió un problema al procesar la solicitud. " . $e->getMessage())
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

            $citasRealizadas = Cita::where("idPaciente", $idPaciente)
                ->where("estado_Cita", "Realizada")
                ->count();

            $citasSinPagar = Cita::where("idPaciente", $idPaciente)
                ->where("estado_Cita", "Sin Pagar")
                ->count();

            $response = [
                "pendientes" => $citasPendientes,
                "canceladas" => $citasCanceladas,
                "confirmadas" => $citasConfirmadas,
                "realizadas" => $citasRealizadas,
                "sin pagar" => $citasSinPagar
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

            if (!empty($pacienteData['password'])) {
                $user = $paciente->user;
                if ($user) {
                    $user->password = Hash::make($pacienteData['password']);
                    $user->save();
                }
            }

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

    public function disablePatient(Request $request, int $id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->activo = false;

            if (!$request->activo) {
                $paciente->idPsicologo = null;
            }

            $paciente->save();

            return HttpResponseHelper::make()
                ->successfulResponse("Paciente deshabilitado y desvinculado con el psicologo correctamente")
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud. " . $e->getMessage()
                )
                ->send();
        }
    }

    public function enablePatient(Request $request, int $id)
    {
        try {

            $request->validate([
                'idPsicologo' => 'required|exists:psicologos,idPsicologo',
            ]);

            $paciente = Paciente::findOrFail($id);

            $psicologo = PSICOLOGO::where('idPsicologo', $request->idPsicologo)
                ->where('estado', 'A')
                ->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->internalErrorResponse("El psicólogo no está activo o no existe.")
                    ->send();
            }

            $paciente->activo = true;
            $paciente->idPsicologo = $psicologo->idPsicologo;
            $paciente->save();
            return HttpResponseHelper::make()
                ->successfulResponse("Paciente habilitado y vinculado con el psicólogo seleccionado.")
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud. " . $e->getMessage()
                )
                ->send();
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $paciente = Paciente::where('email', $request->email)->first();

        if (!$paciente) {
            return response()->json(['message' => 'Paciente no encontrado.'], 404);
        }

        $codigo = rand(10000, 99999);

        $cacheKey = 'codigo_reset_' . $paciente->email;

        Cache::put($cacheKey, $codigo, now()->addMinutes(10));

        Mail::to($paciente->email)->send(new CodigoRecuperacion($codigo));

        return response()->json(['message' => 'Código enviado al correo.']);
    }

    public function verificarCodigo(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string'
        ]);

        $cacheKey = 'codigo_reset_' . $request->email;
        $codigoGuardado = Cache::get($cacheKey);

        if (!$codigoGuardado || $codigoGuardado != $request->code) {
            Log::warning('Código de verificación inválido o expirado.', [
                'email' => $request->email,
                'codigo_enviado' => $codigoGuardado,
                'codigo_recibido' => $request->code,
                'cache_existe' => Cache::has($cacheKey),
            ]);

            return response()->json(['message' => 'Código inválido o expirado.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Paciente no encontrado.'], 404);
        }

        $nuevaContrasena = Str::random(8);
        $user->password = Hash::make($nuevaContrasena);
        $user->save();

        Cache::forget($cacheKey);

        Mail::to($user->email)->send(new NuevaContrasenaGenerada($nuevaContrasena));

        return response()->json(['message' => 'Nueva contraseña enviada al correo.']);
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

            $query = Paciente::where("idPsicologo", $psicologo->idPsicologo)
                ->where("activo", 1)
                ->with([
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

    public function showEnablePaciente(Request $request)
    {
        try {

            $shouldPaginate = $request->query("paginate", false);
            $perPage = $request->query("per_page", 10);

            $query = Paciente::where("activo", 0);

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


    //metodo agregado para pacientes habilitados

    public function showPacientesHabilitados(Request $request)
{
    try {

        $shouldPaginate = $request->query("paginate", false);
        $perPage = $request->query("per_page", 10);

        // Cambiar el filtro para pacientes habilitados (activo = 1)
        $query = Paciente::where("activo", 1);

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
                ->successfulResponse("Pacientes habilitados obtenidos correctamente", [
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
                    "Pacientes habilitados obtenidos correctamente",
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

            if (!$paciente) {
                return HttpResponseHelper::make()
                    ->notFoundResponse("Paciente no encontrado.")
                    ->send();
            }

            $userPassword = optional($paciente->user)->password;

            $response = [
                'idPaciente' => $paciente->idPaciente,
                'codigo' => $paciente->codigo,
                'nombre' => $paciente->nombre,
                'apellido' => $paciente->apellido,
                'email' => $paciente->email,
                'fecha_nacimiento' => $paciente->fecha_nacimiento,
                'imagen' => $paciente->imagen,
                'genero' => $paciente->genero,
                'ocupacion' => $paciente->ocupacion,
                'estadoCivil' => $paciente->estadoCivil,
                'DNI' => $paciente->DNI,
                'celular' => $paciente->celular,
                'direccion' => $paciente->direccion,
                'departamento' => $paciente->departamento,
                'pais' => $paciente->pais,
                'idPsicologo' => $paciente->idPsicologo,
                'user_id' => $paciente->user_id,
                'password' => $userPassword,
            ];

            return HttpResponseHelper::make()
                ->successfulResponse("Paciente obtenido correctamente", $response)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud. " . $e->getMessage()
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

        // Consulta: contar pacientes agrupados por género (o 'Desconocido' si es null/vacío)
        $estadisticasDB = Paciente::where("idPsicologo", $psicologo->idPsicologo)
            ->selectRaw("COALESCE(NULLIF(TRIM(genero), ''), 'Desconocido') as genero, COUNT(*) as cantidad")
            ->groupBy("genero")
            ->get();

        $total = $estadisticasDB->sum("cantidad");
        if ($total === 0) {
            return HttpResponseHelper::make()
                ->successfulResponse("No hay pacientes registrados.", [])
                ->send();
        }

        // Agregar porcentaje
        $estadisticas = $estadisticasDB->mapWithKeys(function ($item) use ($total) {
            return [
                $item->genero => [
                    "cantidad" => $item->cantidad,
                    "porcentaje" => round(($item->cantidad / $total) * 100),
                ],
            ];
        });

        return HttpResponseHelper::make()
            ->successfulResponse(
                "Porcentaje de pacientes por género obtenido correctamente",
                $estadisticas
            )
            ->send();

    } catch (\Exception $e) {
        return HttpResponseHelper::make()
            ->internalErrorResponse(
                "Ocurrió un problema al procesar la solicitud. " . $e->getMessage()
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

            // Calcula edades y agrupar por rangos desde la bd :3
            $estadisticas = Paciente::selectRaw("
                    CASE
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) <= 12 THEN '0-12'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 13 AND 17 THEN '13-17'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
                        ELSE '45+'
                    END as rango,
                    COUNT(*) as total
                ")
                ->where("idPsicologo", $psicologo->idPsicologo)
                ->groupBy("rango")
                ->pluck("total", "rango");

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

    //Uso exclusivo para que Sandro se consuma, todos los pacientes quiere ver
    public function getAllPacientes(){
        try {
            $pacientes = Paciente::where("activo", 1)
                ->with("user")
                ->get();

            return HttpResponseHelper::make()
                ->successfulResponse(
                    "Pacientes obtenidos correctamente",
                    $pacientes
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
