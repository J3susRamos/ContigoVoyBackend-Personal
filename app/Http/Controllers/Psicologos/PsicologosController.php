<?php

namespace App\Http\Controllers\Psicologos;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostUser\PostUser;
use App\Http\Requests\PostPsicologo\PostPsicologo;
use App\Http\Requests\PutPsicologo\PutPsicologo;
use App\Http\Requests\PutUser\PutUser;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Psicologo;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Traits\HttpResponseHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PsicologosController extends Controller
{

    public function createPsicologo(PostPsicologo $requestPsicologo, PostUser $requestUser): JsonResponse
    {
        try {
            $usuarioData = $requestUser->all();
            $usuarioData['rol'] = 'PSICOLOGO';
            $usuarioData['fecha_nacimiento'] = Carbon::createFromFormat('Y-m-d', $usuarioData['fecha_nacimiento'])
                ->format('Y-m-d');
            $usuarioData['password'] = Hash::make($requestUser['password']);

            $usuario = User::create($usuarioData);
            $usuario_id = $usuario->user_id;

            // Asignar el user_id recién creado al psicólogo
            $psicologoData = $requestPsicologo->all();
            $psicologoData['user_id'] = $usuario_id;
            $psicologo = Psicologo::create($psicologoData);

            // Asociar las especialidades y enfoques al psicólogo
            $psicologo->especialidades()->attach($requestPsicologo->input('especialidades'));

            $usuario->assignRole('PSICOLOGO');

            return HttpResponseHelper::make()
                ->successfulResponse('Psicólogo creado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function showById(int $id): JsonResponse
    {
        try {
            $psicologo = Psicologo::with(['especialidades', 'users'])->find($id);

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un psicólogo con el ID proporcionado.')
                    ->send();
            }

            $response = [
                // se modifico 'Titulo' a 'titulo'
                'idPsicologo' => $psicologo->idPsicologo,
                'titulo' => $psicologo->titulo,
                'nombre' => $psicologo->users->name,
                'apellido' => $psicologo->users->apellido,
                'pais' => $psicologo->pais,
                'genero' => $psicologo->genero,
                'correo' => $psicologo->users->email,
                'contraseña' => $psicologo->users->password,
                'imagen' => $psicologo->users->imagen,
                'fecha_nacimiento' => $psicologo->users->fecha_nacimiento->format('d/m/Y'),
                'especialidades' => $psicologo->especialidades->pluck('nombre'),
                'introduccion' => $psicologo->introduccion,
                'experiencia' => $psicologo->experiencia,
            ];

            return HttpResponseHelper::make()
                ->successfulResponse('Psicólogos obtenidos correctamente', $response)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener el psicólogo: ' . $e->getMessage())
                ->send();
        }
    }

  public function showAllPsicologos(Request $request): JsonResponse
{
    try {
        $shouldPaginate = $request->query("paginate", false);
        $perPage = $request->query("per_page", 10);

        $query = Psicologo::with(['especialidades', 'users'])->whereHas('users', function ($q) {
            $q->where("estado", 1);
        });

        if ($request->filled("pais")) {
            $paises = explode(",", $request->query("pais"));
            $query->whereIn("pais", $paises);
        }

        if ($request->filled("genero")) {
            $generos = explode(",", $request->query("genero"));
            $query->whereIn("genero", $generos);
        }

        if ($request->filled("idioma")) {
            $idiomas = explode(",", $request->query("idioma"));
            $query->whereIn("idioma", $idiomas);
        }

        if ($request->filled("enfoque")) {
            $enfoques = explode(",", $request->query("enfoque"));
            
            // ✅ NUEVO: Mapeo de enfoques a títulos de psicólogos
            $mapeoEnfoques = [
                'niños' => 'Pediatra',
                'adolescentes' => 'Pedagogo', 
                'familiar' => 'Psicoanalista',
                'pareja' => 'Terapeuta',
                'adulto' => 'Conductual'
            ];
            
            $titulosFiltro = [];
            foreach ($enfoques as $enfoque) {
                if (isset($mapeoEnfoques[$enfoque])) {
                    $titulosFiltro[] = $mapeoEnfoques[$enfoque];
                }
            }
            
            // ✅ FILTRO ORIGINAL MANTENIDO (búsqueda en especialidades)
            $query->whereHas("especialidades", function ($q) use ($enfoques) {
                $q->whereIn("nombre", $enfoques);
            });
            
            // ✅ NUEVO FILTRO AGREGADO (búsqueda en título del psicólogo)
            if (!empty($titulosFiltro)) {
                $query->orWhereIn("titulo", $titulosFiltro);
            }
        }
        //agregado especialidad M.
if ($request->filled("especialidad")) {
    $especialidades = explode(",", $request->query("especialidad"));
    $query->whereHas("especialidades", function ($q) use ($especialidades) {
        $q->whereIn("nombre", $especialidades);
    });
}
        if ($request->filled("search")) {
            $search = $request->query("search");
            $query->whereHas("users", function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                    ->orWhere("apellido", "like", "%{$search}%");
            });
        }

        if ($shouldPaginate) {
            $paginator = $query->paginate($perPage);
            $data = collect($paginator->items())->map(function ($psicologo) {
                return $this->mapPsicologo($psicologo);
            });

            return HttpResponseHelper::make()
                ->successfulResponse("Psicólogos obtenidos correctamente", [
                    "data" => $data,
                    "pagination" => [
                        "current_page" => $paginator->currentPage(),
                        "last_page" => $paginator->lastPage(),
                        "per_page" => $paginator->perPage(),
                        "total" => $paginator->total(),
                    ],
                ])
                ->send();
        } else {
            $psicologos = $query->get();
            $data = $psicologos->map(function ($psicologo) {
                return $this->mapPsicologo($psicologo);
            });

            return HttpResponseHelper::make()
                ->successfulResponse("Psicólogos obtenidos correctamente", $data)
                ->send();
        }
    } catch (\Exception $e) {
        return HttpResponseHelper::make()
            ->internalErrorResponse("Error al obtener psicólogos: " . $e->getMessage())
            ->send();
    }
}
    public function listarNombre(): JsonResponse
    {
        try {
            $psicologos = Psicologo::with('users')
                ->whereHas('users', function ($q) {
                    $q->where("estado", 1);
                })
                ->get(['idPsicologo', 'user_id'])
                ->map(function ($psicologo) {
                    return [
                        'idPsicologo' => $psicologo->idPsicologo,
                        'nombre' => $psicologo->users->name,
                        'apellido' => $psicologo->users->apellido,
                    ];
                });
            return HttpResponseHelper::make()
                ->successfulResponse("Psicólogos obtenidos correctamente", $psicologos)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Error al obtener psicólogos: " . $e->getMessage())
                ->send();
        }
    }

    public function showInactivePsicologos(Request $request): JsonResponse
    {
        try {
            $shouldPaginate = $request->query("paginate", false);
            $perPage = $request->query("per_page", 10);

            $query = Psicologo::with(['especialidades', 'users'])->whereHas('users', function ($q) {
                $q->where("estado", 0);
            });

            if ($request->filled("pais")) {
                $paises = explode(",", $request->query("pais"));
                $query->whereIn("pais", $paises);
            }

            if ($request->filled("genero")) {
                $generos = explode(",", $request->query("genero"));
                $query->whereIn("genero", $generos);
            }
//Agregado M.
         if ($request->filled("idioma")) {
    $idiomas = explode(",", $request->query("idioma"));
    
    $query->where(function($q) use ($idiomas) {
        foreach ($idiomas as $idioma) {
            // Buscar psicólogos que tengan el idioma en su lista (incluyendo múltiples idiomas)
            $q->orWhere('idioma', 'LIKE', '%' . $idioma . '%');
        }
    });
}

//Fin Agregado M.

            if ($request->filled("enfoque")) {
                $enfoques = explode(",", $request->query("enfoque"));
                $query->whereHas("especialidades", function ($q) use ($enfoques) {
                    $q->whereIn("nombre", $enfoques);
                });
            }

            if ($request->filled("search")) {
                $search = $request->query("search");
                $query->whereHas("users", function ($q) use ($search) {
                    $q->where("name", "like", "%{$search}%")
                        ->orWhere("apellido", "like", "%{$search}%");
                });
            }

            if ($shouldPaginate) {
                $paginator = $query->paginate($perPage);
                $data = collect($paginator->items())->map(function ($psicologo) {
                    return $this->mapPsicologo($psicologo);
                });

                return HttpResponseHelper::make()
                    ->successfulResponse("Psicólogos obtenidos correctamente", [
                        "data" => $data,
                        "pagination" => [
                            "current_page" => $paginator->currentPage(),
                            "last_page" => $paginator->lastPage(),
                            "per_page" => $paginator->perPage(),
                            "total" => $paginator->total(),
                        ],
                    ])
                    ->send();
            } else {
                $psicologos = $query->get();
                $data = $psicologos->map(function ($psicologo) {
                    return $this->mapPsicologo($psicologo);
                });

                return HttpResponseHelper::make()
                    ->successfulResponse("Psicólogos obtenidos correctamente", $data)
                    ->send();
            }
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Error al obtener psicólogos: " . $e->getMessage())
                ->send();
        }
    }

    private function mapPsicologo($psicologo): array
    {
        return [
            'idPsicologo' => $psicologo->idPsicologo,
            'titulo' => $psicologo->titulo,
            'nombre' => $psicologo->users->name,
            'apellido' => $psicologo->users->apellido,
            'pais' => $psicologo->pais,
            'edad' => $psicologo->users->edad,
            'genero' => $psicologo->genero,
            'experiencia' => $psicologo->experiencia,
            'especialidades' => $psicologo->especialidades->pluck('nombre'),
            'introduccion' => $psicologo->introduccion,
            'horario' => $psicologo->horario,
            'correo' => $psicologo->users->email,
            'imagen' => $psicologo->users->imagen,
        ];
    }

    public function updatePsicologo(PutPsicologo $requestPsicologo, PutUser $requestUser, int $id): JsonResponse
    {
        try {
            $psicologo = Psicologo::findOrFail($id);
            $usuario = User::findOrFail($psicologo->user_id);
            $psicologoData = $requestPsicologo->only([
                'titulo',
                'introduccion',
                'pais',
                'genero',
                'experiencia',
                'horario'
            ]);
            $psicologo->update($psicologoData);



            $usuarioData = $requestUser->only(['apellido', 'email', 'password', 'fecha_nacimiento', 'imagen']);
            if ($requestUser->filled('nombre')) {
                $usuarioData['name'] = $requestUser->input('nombre');
            }
            if ($requestUser->filled('password')) {
                $usuarioData['password'] = Hash::make($requestUser->password);
            }
            if ($requestUser->filled('fecha_nacimiento')) {
                $usuarioData['fecha_nacimiento'] = Carbon::createFromFormat('d/m/Y', $requestUser->fecha_nacimiento)->format('Y-m-d');
            }
            $usuario->update($usuarioData);
            if ($requestPsicologo->filled('especialidades')) {
                $especialidadesNombres = $requestPsicologo->input('especialidades');
                $especialidadesIds = [];
                foreach ($especialidadesNombres as $nombre) {
                    $nombre = trim($nombre);
                    if (empty($nombre)) {
                        continue;
                    }
                    $especialidad = Especialidad::firstOrCreate(['nombre' => $nombre]);
                    if (!$especialidad->idEspecialidad) {
                        throw new \Exception("No se pudo crear o encontrar la especialidad: $nombre");
                    }
                    $especialidadesIds[] = $especialidad->idEspecialidad;
                }
                if (!empty($especialidadesIds)) {
                    $psicologo->especialidades()->sync($especialidadesIds);
                }
            }

            return HttpResponseHelper::make()
                ->successfulResponse('Psicólogo actualizado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema: ' . $e->getMessage())
                ->send();
        }
    }

//solo actualiza ahora imagen, nombre, apellido,Introducción Profesional, idioma y especialidades  M.
public function actualizarPsicologo(Request $request, int $id): JsonResponse
{
    try {
        $psicologo = Psicologo::findOrFail($id);
        $usuario = User::findOrFail($psicologo->user_id);

        // Actualizar datos del usuario
        $usuarioData = [];
        if ($request->filled('nombre')) {
            $usuarioData['name'] = $request->input('nombre');
        }
        if ($request->filled('apellido')) {
            $usuarioData['apellido'] = $request->input('apellido');
        }
        if ($request->filled('imagen')) {
            $usuarioData['imagen'] = $request->input('imagen');
        }
        if ($request->filled('fecha_nacimiento')) {
            $usuarioData['fecha_nacimiento'] = $request->input('fecha_nacimiento');
        }
        if (!empty($usuarioData)) {
            $usuario->update($usuarioData);
        }

        // Actualizar datos del psicólogo (AGREGAR ESTA PARTE)
        $psicologoData = [];
        if ($request->filled('titulo')) {
            $psicologoData['titulo'] = $request->input('titulo');
        }
        if ($request->filled('introduccion')) {
            $psicologoData['introduccion'] = $request->input('introduccion');
        }
        if ($request->filled('pais')) {
            $psicologoData['pais'] = $request->input('pais');
        }
        if ($request->filled('genero')) {
            $psicologoData['genero'] = $request->input('genero');
        }
        if ($request->filled('experiencia')) {
            $psicologoData['experiencia'] = $request->input('experiencia');
        }
        // --- AGREGADO: CAMPO IDIOMA ---
        if ($request->filled('idioma')) {
            $psicologoData['idioma'] = $request->input('idioma');
        }
        // --- FIN AGREGADO ---
        
        if (!empty($psicologoData)) {
            $psicologo->update($psicologoData);
        }

        // Resto del código para especialidades...
        if ($request->filled('especialidades')) {
            $especialidadesNombres = $request->input('especialidades');
            $especialidadesIds = [];
            foreach ($especialidadesNombres as $nombre) {
                $nombre = trim($nombre);
                if (empty($nombre)) {
                    continue;
                }
                $especialidad = Especialidad::firstOrCreate(['nombre' => $nombre]);
                if (!$especialidad->idEspecialidad) {
                    throw new \Exception("No se pudo crear o encontrar la especialidad: $nombre");
                }
                $especialidadesIds[] = $especialidad->idEspecialidad;
            }
            if (!empty($especialidadesIds)) {
                $psicologo->especialidades()->sync($especialidadesIds);
            }
        }

        return HttpResponseHelper::make()
            ->successfulResponse('Psicólogo actualizado correctamente')
            ->send();
    } catch (\Exception $e) {
        return HttpResponseHelper::make()
            ->internalErrorResponse('Ocurrió un problema: ' . $e->getMessage())
            ->send();
    }
}

    //Obtener las especialidades de un psicologo
    public function obtenerEspecialidades(int $id): JsonResponse
    {
        try {
            $psicologo = Psicologo::with('especialidades')->find($id);

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un psicólogo con el ID proporcionado.')
                    ->send();
            }

            $especialidades = $psicologo->especialidades->pluck('nombre');

            return HttpResponseHelper::make()
                ->successfulResponse('Especialidades obtenidas correctamente', $especialidades)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener las especialidades: ' . $e->getMessage())
                ->send();
        }
    }

    public function cambiarEstadoPsicologo(int $id): JsonResponse
    {
        try {
            $psicologo = Psicologo::with('users')->find($id);

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un psicólogo con el ID proporcionado.')
                    ->send();
            }

            if ($psicologo->users) {
                $psicologo->users->estado = $psicologo->users->estado == 0 ? 1 : 0;
                $psicologo->users->save();

                return HttpResponseHelper::make()
                    ->successfulResponse(
                        'Estado del usuario del psicólogo cambiado correctamente a ' .
                            ($psicologo->users->estado === 1 ? 'Activo' : 'Inactivo')
                    )
                    ->send();
            } else {
                return HttpResponseHelper::make()
                    ->notFoundResponse('El psicólogo no tiene un usuario asociado.')
                    ->send();
            }
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al cambiar el estado del psicólogo: ' . $e->getMessage())
                ->send();
        }
    }

    public function psicologoDashboard(): JsonResponse
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

        $totalMinutosReservados = Cita::where('idPsicologo', $idPsicologo)
            ->whereIn('estado_Cita', ['completada', 'pendiente'])
            ->sum('duracion');

        // Total de pacientes únicos
        $totalPacientes = Cita::where('idPsicologo', $idPsicologo)
            ->whereNotNull('idPaciente')
            ->distinct('idPaciente')
            ->count('idPaciente');

        // Nuevos pacientes en los últimos 30 días (por su primera cita)
        $nuevosPacientes = Cita::select('idPaciente')
            ->where('idPsicologo', $idPsicologo)
            ->whereNotNull('idPaciente')
            ->selectRaw('MIN(fecha_Cita) as primera_cita, idPaciente')
            ->groupBy('idPaciente')
            ->havingRaw('primera_cita >= ?', [now()->subDays(30)])
            ->get()
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
            ])
            ->send();
    }

    public function DeletePsicologo(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Buscar el psicólogo
            $psicologo = Psicologo::find($id);

            if (!$psicologo) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Psicólogo no encontrado',
                ], 404);
            }

            // Eliminar citas asociadas
            DB::table('citas')->where('idPsicologo', $psicologo->idPsicologo)->delete();

            // Obtener el user_id del psicólogo
            $userId = $psicologo->user_id;

            // Eliminar el psicólogo
            DB::table('psicologos')->where('idPsicologo', $id)->delete();

            // Eliminar el usuario
            DB::table('users')->where('user_id', $userId)->delete();

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Psicólogo, sus citas y su usuario eliminados correctamente',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status_code' => 500,
                'status_message' => 'Internal Server Error',
                'description' => 'Ocurrió un problema al eliminar el psicólogo: ' . $e->getMessage(),
            ], 500);
        }
    }

     // AGREGAR ESTE MÉTODO NUEVO PARA OBTENER IDIOMAS DISPONIBLES M.
    public function getIdiomasDisponibles(): JsonResponse
{
    try {
        // Obtener todos los idiomas únicos que usan los psicólogos
        $idiomas = Psicologo::whereNotNull('idioma')
            ->select('idioma')
            ->distinct()
            ->get()
            ->pluck('idioma')
            ->filter()
            ->flatMap(fn($i) => array_map('trim', explode(',', $i)))
            ->unique()
            ->values();

        if ($idiomas->isEmpty()) {
            $idiomas = collect(['es', 'en', 'fr', 'de', 'pt', 'it']);
        }

        $idiomas = $idiomas->map(fn($codigo) => [
            'codigo' => $codigo,
            'nombre' => [
                'es' => 'Español',
                'en' => 'Inglés',
                'fr' => 'Francés',
                'de' => 'Alemán',
                'pt' => 'Portugués',
                'it' => 'Italiano',
            ][$codigo] ?? $codigo,
        ]);

        // 👇 Aquí el return que faltaba
        return HttpResponseHelper::make()
            ->successfulResponse('Idiomas obtenidos correctamente', $idiomas)
            ->send();

    } catch (\Exception $e) {
        return HttpResponseHelper::make()
            ->internalErrorResponse('Error al obtener idiomas: ' . $e->getMessage())
            ->send();
    }
    }
}
