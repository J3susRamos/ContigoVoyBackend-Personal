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
use App\Models\Idioma;
use Illuminate\Support\Facades\Hash;
use App\Traits\HttpResponseHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsicologosController extends Controller
{
    /**
     * Normaliza nombres (trim, lower → Title) para evitar duplicados tipo "ingles", "Ingles", "inglés".
     */
    private function normalizeName(?string $s): ?string
    {
        if ($s === null)
            return null;
        $s = trim($s);
        if ($s === '')
            return null;
        $s = mb_strtolower($s, 'UTF-8');
        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }

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

            // Crear psicólogo
            $psicologoData = $requestPsicologo->all();
            $psicologoData['user_id'] = $usuario_id;

            //Link de Google Meet
            $psicologoData['meet_link'] = $requestPsicologo->input('meet_link');

            // No guardamos 'idioma' como string: ahora usamos relación N:M
            unset($psicologoData['idioma']);

            $psicologo = Psicologo::create($psicologoData);

            // Asociar especialidades (si llegan)
            if ($requestPsicologo->filled('especialidades')) {
                $psicologo->especialidades()->attach($requestPsicologo->input('especialidades'));
            }

            // createPsicologo
            if ($requestPsicologo->filled('idiomas') && is_array($requestPsicologo->input('idiomas'))) {
                $ids = [];
                foreach ($requestPsicologo->input('idiomas') as $val) {
                    $nom = $this->normalizeName($val);
                    if (!$nom)
                        continue;
                    $idioma = Idioma::firstOrCreate(['nombre' => $nom]);
                    $ids[] = $idioma->idIdioma;
                }
                if (!empty($ids)) {
                    $psicologo->idiomas()->sync($ids);
                }
            }

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
            $psicologo = Psicologo::with(['especialidades', 'idiomas', 'users'])->find($id);

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un psicólogo con el ID proporcionado.')
                    ->send();
            }

            $response = [
                'idPsicologo' => $psicologo->idPsicologo,
                'titulo' => $psicologo->titulo,
                'nombre' => $psicologo->users->name,
                'apellido' => $psicologo->users->apellido,
                'pais' => $psicologo->pais,
                'genero' => $psicologo->genero,
                'correo' => $psicologo->users->email,
                'contraseña' => $psicologo->users->password,
                'imagen' => $psicologo->users->imagen,
                'fecha_nacimiento' => $psicologo->users->fecha_nacimiento?->format('d/m/Y'),
                'especialidades' => $psicologo->especialidades->pluck('nombre'),
                'idiomas' => $psicologo->idiomas->pluck('nombre'),
                'introduccion' => $psicologo->introduccion,
                'experiencia' => $psicologo->experiencia,
                'meet_link' => $psicologo->meet_link,
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

            $query = Psicologo::with(['especialidades', 'idiomas', 'users'])
                ->whereHas('users', function ($q) {
                    $q->where("estado", 1);
                });

            if ($request->filled("pais")) {
                $paises = array_map('trim', explode(",", $request->query("pais")));
                $query->whereIn("pais", $paises);
            }

            if ($request->filled("genero")) {
                $generos = array_map('trim', explode(",", $request->query("genero")));
                $query->whereIn("genero", $generos);
            }

            // Idiomas por relación N:M (nombres)
            if ($request->filled("idioma")) {
                $idiomas = array_map(fn($v) => $this->normalizeName($v), explode(",", $request->query("idioma")));
                $idiomas = array_values(array_filter($idiomas));
                if (!empty($idiomas)) {
                    $query->whereHas('idiomas', function ($q) use ($idiomas) {
                        $q->whereIn('nombre', $idiomas);
                    });
                }
            }

            // Enfoque (mapeo a titulo ó por especialidad)
            if ($request->filled("enfoque")) {
                $enfoques = array_map('trim', explode(",", $request->query("enfoque")));

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

                // Agrupar para no romper otros filtros
                $query->where(function ($q) use ($enfoques, $titulosFiltro) {
                    $q->whereHas("especialidades", function ($qq) use ($enfoques) {
                        $qq->whereIn("nombre", $enfoques);
                    });
                    if (!empty($titulosFiltro)) {
                        $q->orWhereIn("titulo", $titulosFiltro);
                    }
                });
            }

            // Especialidad explícita
            if ($request->filled("especialidad")) {
                $especialidades = array_map('trim', explode(",", $request->query("especialidad")));
                $query->whereHas("especialidades", function ($q) use ($especialidades) {
                    $q->whereIn("nombre", $especialidades);
                });
            }

            // Búsqueda por nombre y apellido del usuario
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

            $query = Psicologo::with(['especialidades', 'idiomas', 'users'])
                ->whereHas('users', function ($q) {
                    $q->where("estado", 0);
                });

            if ($request->filled("pais")) {
                $paises = array_map('trim', explode(",", $request->query("pais")));
                $query->whereIn("pais", $paises);
            }

            if ($request->filled("genero")) {
                $generos = array_map('trim', explode(",", $request->query("genero")));
                $query->whereIn("genero", $generos);
            }

            // Idiomas N:M por nombre
            if ($request->filled("idioma")) {
                $idiomas = array_map(fn($v) => $this->normalizeName($v), explode(",", $request->query("idioma")));
                $idiomas = array_values(array_filter($idiomas));
                if (!empty($idiomas)) {
                    $query->whereHas('idiomas', function ($q) use ($idiomas) {
                        $q->whereIn('nombre', $idiomas);
                    });
                }
            }

            if ($request->filled("enfoque")) {
                $enfoques = array_map('trim', explode(",", $request->query("enfoque")));
                $query->whereHas("especialidades", function ($q) use ($enfoques) {
                    $q->whereIn("nombre", $enfoques);
                });
            }

            if ($request->filled("especialidad")) {
                $especialidades = array_map('trim', explode(",", $request->query("especialidad")));
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
            'idiomas' => $psicologo->idiomas->pluck('nombre'),
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
                'horario',
                'meet_link'
            ]);

            // No usamos 'idioma' string
            unset($psicologoData['idioma']);

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

            // Especialidades (por nombre)
            if ($requestPsicologo->filled('especialidades')) {
                $especialidadesNombres = $requestPsicologo->input('especialidades');
                $especialidadesIds = [];
                foreach ($especialidadesNombres as $nombre) {
                    $nombre = trim($nombre);
                    if (empty($nombre))
                        continue;
                    $esp = Especialidad::firstOrCreate(['nombre' => $nombre]);
                    $especialidadesIds[] = $esp->idEspecialidad;
                }
                if (!empty($especialidadesIds)) {
                    $psicologo->especialidades()->sync($especialidadesIds);
                }
            }

            // Idiomas (array de strings por nombre)
            if ($requestPsicologo->filled('idiomas')) {
                $entradas = $requestPsicologo->input('idiomas');
                $ids = [];
                foreach ($entradas as $val) {
                    $nom = $this->normalizeName($val);
                    if (!$nom)
                        continue;
                    $idioma = Idioma::firstOrCreate(['nombre' => $nom]);
                    $ids[] = $idioma->idIdioma;
                }
                $psicologo->idiomas()->sync($ids);
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

    // Actualiza imagen, nombre, apellido, presentación, país, género, experiencia, idiomas y especialidades
    public function actualizarPsicologo(Request $request, int $id): JsonResponse
    {
        try {
            $psicologo = Psicologo::findOrFail($id);
            $usuario = User::findOrFail($psicologo->user_id);

            // Usuario
            $usuarioData = [];
            if ($request->filled('nombre'))
                $usuarioData['name'] = $request->input('nombre');
            if ($request->filled('apellido'))
                $usuarioData['apellido'] = $request->input('apellido');
            if ($request->filled('imagen'))
                $usuarioData['imagen'] = $request->input('imagen');
            if ($request->filled('fecha_nacimiento'))
                $usuarioData['fecha_nacimiento'] = $request->input('fecha_nacimiento');
            if (!empty($usuarioData))
                $usuario->update($usuarioData);

            // Psicólogo
            $psicologoData = [];
            foreach (['titulo', 'introduccion', 'pais', 'genero', 'experiencia', 'horario', 'meet_link'] as $k) {
                if ($request->filled($k))
                    $psicologoData[$k] = $request->input($k);
            }
            // NO escribimos 'idioma' como string
            unset($psicologoData['idioma']);

            if (!empty($psicologoData))
                $psicologo->update($psicologoData);

            // Especialidades (array de nombres)
            if ($request->filled('especialidades')) {
                $especialidadesNombres = $request->input('especialidades');
                $especialidadesIds = [];
                foreach ($especialidadesNombres as $nombre) {
                    $nombre = trim($nombre);
                    if (empty($nombre))
                        continue;
                    $esp = Especialidad::firstOrCreate(['nombre' => $nombre]);
                    $especialidadesIds[] = $esp->idEspecialidad;
                }
                if (!empty($especialidadesIds)) {
                    $psicologo->especialidades()->sync($especialidadesIds);
                }
            }

            // Idiomas (array de nombres)
            if ($request->filled('idiomas')) {
                $entradas = $request->input('idiomas');
                $ids = [];
                foreach ($entradas as $val) {
                    $nom = $this->normalizeName($val);
                    if (!$nom)
                        continue;
                    $idioma = Idioma::firstOrCreate(['nombre' => $nom]);
                    $ids[] = $idioma->idIdioma;
                }
                $psicologo->idiomas()->sync($ids);
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

    // Obtener especialidades de un psicólogo
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

        $totalCitas = Cita::where('idPsicologo', $idPsicologo)->count();
        $citasCompletadas = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'completada')->count();
        $citasPendientes = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'pendiente')->count();
        $citasCanceladas = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'cancelada')->count();

        $totalMinutosReservados = Cita::where('idPsicologo', $idPsicologo)
            ->whereIn('estado_Cita', ['completada', 'pendiente'])
            ->sum('duracion');

        $totalPacientes = Cita::where('idPsicologo', $idPsicologo)
            ->whereNotNull('idPaciente')
            ->distinct('idPaciente')
            ->count('idPaciente');

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

            $psicologo = Psicologo::find($id);

            if (!$psicologo) {
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Psicólogo no encontrado',
                ], 404);
            }

            DB::table('citas')->where('idPsicologo', $psicologo->idPsicologo)->delete();

            $userId = $psicologo->user_id;

            DB::table('psicologos')->where('idPsicologo', $id)->delete();
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

    /**
     * Devuelve idiomas disponibles (desde tabla idiomas) – útil para catálogos.
     */
    public function getIdiomasDisponibles(): JsonResponse
    {
        try {
            $idiomas = Idioma::orderBy('nombre')->get(['idIdioma', 'nombre'])->pluck('nombre');

            if ($idiomas->isEmpty()) {
                // fallback opcional
                $idiomas = collect(['Español', 'Inglés', 'Francés', 'Alemán', 'Portugués', 'Italiano']);
            }

            return HttpResponseHelper::make()
                ->successfulResponse('Idiomas obtenidos correctamente', $idiomas->values())
                ->send();

        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener idiomas: ' . $e->getMessage())
                ->send();
        }
    }

    /**
     * Opciones dinámicas de filtros basadas en psicólogos activos y catálogos.
     * Alias: /api/psicologos/filters y /api/psicologos/filter-options (defínelo en routes).
     */
    public function getFilterOptions(): JsonResponse
    {
        try {
            $activos = Psicologo::with([
                'idiomas',
                'users' => function ($q) {
                    $q->where('estado', 1);
                }
            ])
                ->whereHas('users', fn($q) => $q->where('estado', 1))
                ->get(['idPsicologo', 'pais', 'genero', 'titulo']);

            $paises = $activos->pluck('pais')->filter()->unique()->values();
            $generos = $activos->pluck('genero')->filter()->unique()->values();

            $idiomas = $activos->flatMap(fn($p) => $p->idiomas->pluck('nombre'))
                ->filter()
                ->unique()
                ->values();

            $especialidades = Especialidad::whereHas('psicologos', function ($q) {
                $q->whereHas('users', function ($u) {
                    $u->where('estado', 1); // solo psicólogos con user activo
                });
            })
                ->orderBy('nombre')
                ->pluck('nombre')
                ->filter()
                ->unique()
                ->values();

            // Enfoques mediante mapeo a título y/o presencia como especialidad
            $mapeoEnfoques = [
                'niños' => 'Pediatra',
                'adolescentes' => 'Pedagogo',
                'familiar' => 'Psicoanalista',
                'pareja' => 'Terapeuta',
                'adulto' => 'Conductual'
            ];

            $titulosActivos = $activos->pluck('titulo')->filter()->unique()->values()->all();
            $enfoques = [];
            foreach ($mapeoEnfoques as $clave => $titulo) {
                $existeTitulo = in_array($titulo, $titulosActivos, true);
                $existeEspecialidad = Especialidad::where('nombre', $clave)->exists();
                if ($existeTitulo || $existeEspecialidad) {
                    $enfoques[] = $clave;
                }
            }

            $result = [
                'paises' => $paises,
                'generos' => $generos,
                'idiomas' => $idiomas,
                'enfoques' => array_values(array_unique($enfoques)),
                'especialidades' => $especialidades,
            ];
            return HttpResponseHelper::make()
                ->successfulResponse('Opciones de filtros obtenidas correctamente', $result)
                ->send();

        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener opciones de filtros: ' . $e->getMessage())
                ->send();
        }
    }
}
