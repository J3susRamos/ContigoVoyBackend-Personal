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
            $psicologo = Psicologo::with(['especialidades', 'user'])->find($id);

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un psicólogo con el ID proporcionado.')
                    ->send();
            }

            $response = [
                // se modifico 'Titulo' a 'titulo'
                'idPsicologo' => $psicologo->idPsicologo,
                'titulo' => $psicologo->titulo,
                'nombre' => $psicologo->user->name,
                'apellido' => $psicologo->user->apellido,
                'pais' => $psicologo->pais,
                'genero' => $psicologo->genero,
                'correo' => $psicologo->user->email,
                'contraseña' => $psicologo->user->password,
                'imagen' => $psicologo->user->imagen,
                'fecha_nacimiento' => $psicologo->user->fecha_nacimiento->format('d/m/Y'),
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

            $query = Psicologo::with(['especialidades', 'user'])->whereHas('user', function($q){
                $q->where("estado",1);
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
                $query->whereHas("especialidades", function ($q) use ($enfoques) {
                    $q->whereIn("nombre", $enfoques);
                });
            }

            if ($request->filled("search")) {
                $search = $request->query("search");
                $query->whereHas("user", function ($q) use ($search) {
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
        try{
            $psicologos = Psicologo::with('user')
                ->whereHas('user', function($q){
                $q->where("estado",1);
                })
                ->get(['idPsicologo', 'user_id'])
                ->map(function($psicologo){
                    return [
                        'idPsicologo' => $psicologo->idPsicologo,
                        'nombre' => $psicologo->user->name,
                        'apellido' => $psicologo->user->apellido,
                    ];
                });
            return HttpResponseHelper::make()
                ->successfulResponse("Psicólogos obtenidos correctamente", $psicologos)
                ->send();
        }catch(\Exception $e){
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

            $query = Psicologo::with(['especialidades', 'user'])->whereHas('user', function($q){
                $q->where("estado",0);
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
                $query->whereHas("especialidades", function ($q) use ($enfoques) {
                    $q->whereIn("nombre", $enfoques);
                });
            }

            if ($request->filled("search")) {
                $search = $request->query("search");
                $query->whereHas("user", function ($q) use ($search) {
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
            'nombre' => $psicologo->user->name,
            'apellido' => $psicologo->user->apellido,
            'pais' => $psicologo->pais,
            'edad' => $psicologo->user->edad,
            'genero' => $psicologo->genero,
            'experiencia' => $psicologo->experiencia,
            'especialidades' => $psicologo->especialidades->pluck('nombre'),
            'introduccion' => $psicologo->introduccion,
            'horario' => $psicologo->horario,
            'correo' => $psicologo->user->email,
            'imagen' => $psicologo->user->imagen,
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

    //solo actualizar imagen, nombre, apellido y especialidades
    public function actualizarPsicologo(Request $request, int $id): JsonResponse
    {
        try {
            $psicologo = Psicologo::findOrFail($id);
            $usuario = User::findOrFail($psicologo->user_id);

            $usuarioData = [];
            if ($request->filled('name')) {
                $usuarioData['name'] = $request->input('name');
            }
            if ($request->filled('apellido')) {
                $usuarioData['apellido'] = $request->input('apellido');
            }
            if ($request->filled('imagen')) {
                $usuarioData['imagen'] = $request->input('imagen');
            }
            if (!empty($usuarioData)) {
                $usuario->update($usuarioData);
            }

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
    public function obtenerEspecialidades(int $id): JsonResponse{
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
            $psicologo = Psicologo::with('user')->find($id);

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un psicólogo con el ID proporcionado.')
                    ->send();
            }

            if ($psicologo->user) {
                $psicologo->user->estado = $psicologo->user->estado === '0' ? '1' : '0';
                $psicologo->user->save();

                return HttpResponseHelper::make()
                    ->successfulResponse(
                        'Estado del usuario del psicólogo cambiado correctamente a ' .
                        ($psicologo->user->estado === '1' ? 'Activo' : 'Inactivo')
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

}