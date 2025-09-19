<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostUser\PostUser;
use App\Traits\HttpResponseHelper;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function register(PostUser $request): JsonResponse
    {
        try {
            $userData = $request->all();
            $userData['password'] = Hash::make($request['password']);
            $userData['fecha_nacimiento'] = Carbon::createFromFormat('d / m / Y', $userData['fecha_nacimiento'])->format('Y-m-d');
            $image = $request->file('imagen');
            $userData['imagen'] = base64_encode(file_get_contents($image->getRealPath()));

            $user = User::create($userData);
            $user->assignRole('ADMIN');

            return HttpResponseHelper::make()
                ->successfulResponse('Usuario creado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }


    public function getUsersByRole(Request $request){
        try {
            $perPage = $request->query("per_page", 10);
            $rol = $request->query("rol","PSICOLOGO");
            $query = User::with([])->where('rol', $rol);

            $paginator = $query->paginate($perPage);

            return HttpResponseHelper::make()
                ->successfulResponse("Usuarios obtenidos correctamente", [
                    "data" => $paginator,
                ])
                ->send();


        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Error al obtener usuarios: " . $e->getMessage())
                ->send();
        }
    }

    /**
     * Obtener todos los trabajadores (excluye PACIENTE) agrupados por rol
     */
    public function getAllWorkers(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query("per_page", 50);
            $estado = $request->query("estado"); // true, false o null para todos
            $rol = $request->query("rol"); // filtrar por rol específico si se proporciona

            $query = User::query()
                ->whereNotIn('rol', ['PACIENTE'])
                ->select('user_id', 'name', 'apellido', 'email', 'rol', 'estado', 'fecha_creacion');

            // Filtrar por estado si se especifica
            if ($estado !== null) {
                $query->where('estado', $estado === 'true');
            }

            // Filtrar por rol si se especifica
            if ($rol && $rol !== 'ALL') {
                $query->where('rol', $rol);
            }

            $query->orderBy('rol')->orderBy('name');
            $paginator = $query->paginate($perPage);

            return HttpResponseHelper::make()
                ->successfulResponse("Trabajadores obtenidos correctamente", [
                    "data" => $paginator,
                ])
                ->send();

        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Error al obtener trabajadores: " . $e->getMessage())
                ->send();
        }
    }

    /**
     * Cambiar el rol de un usuario trabajador
     */
    public function changeUserRole(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,user_id',
                'nuevo_rol' => 'required|string|in:ADMIN,PSICOLOGO,MARKETING,COMUNICACION'
            ]);

            $user = User::find($request->user_id);

            // Proteger el super admin - no se puede cambiar su rol
            if ($user->email === 'admin@gmail.com') {
                return HttpResponseHelper::make()
                    ->badRequestResponse("No se puede modificar el rol del administrador principal")
                    ->send();
            }

            // Verificar que no sea un paciente
            if ($user->rol === 'PACIENTE') {
                return HttpResponseHelper::make()
                    ->badRequestResponse("No se puede cambiar el rol de un paciente desde esta sección")
                    ->send();
            }

            $oldRole = $user->rol;
            $user->rol = $request->nuevo_rol;
            $user->save();

            // Si usas Spatie Roles, también actualizar allí
            if (method_exists($user, 'syncRoles')) {
                $user->syncRoles([$request->nuevo_rol]);
            }

            return HttpResponseHelper::make()
                ->successfulResponse("Rol actualizado correctamente de {$oldRole} a {$request->nuevo_rol}")
                ->send();

        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Error al cambiar rol: " . $e->getMessage())
                ->send();
        }
    }

    /**
     * Habilitar/Deshabilitar un usuario trabajador
     */
    public function toggleUserStatus(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,user_id',
                'estado' => 'required|boolean'
            ]);

            $user = User::find($request->user_id);

            // Proteger el super admin - no se puede deshabilitar
            if ($user->email === 'admin@gmail.com') {
                return HttpResponseHelper::make()
                    ->badRequestResponse("No se puede modificar el estado del administrador principal")
                    ->send();
            }

            // Verificar que no sea un paciente
            if ($user->rol === 'PACIENTE') {
                return HttpResponseHelper::make()
                    ->badRequestResponse("No se puede cambiar el estado de un paciente desde esta sección")
                    ->send();
            }

            $user->estado = $request->estado;
            $user->save();

            $action = $request->estado ? 'habilitado' : 'deshabilitado';

            return HttpResponseHelper::make()
                ->successfulResponse("Usuario {$action} correctamente")
                ->send();

        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Error al cambiar estado: " . $e->getMessage())
                ->send();
        }
    }    /**
     * Obtener estadísticas de trabajadores por rol
     */
    public function getWorkersStats(): JsonResponse
    {
        try {
            $stats = User::whereNotIn('rol', ['PACIENTE'])
                ->selectRaw('rol, estado, COUNT(*) as count')
                ->groupBy('rol', 'estado')
                ->get()
                ->groupBy('rol')
                ->map(function ($items) {
                    $total = $items->sum('count');
                    $activos = $items->where('estado', 1)->sum('count');
                    $inactivos = $items->where('estado', 0)->sum('count');

                    return [
                        'total' => $total,
                        'activos' => $activos,
                        'inactivos' => $inactivos
                    ];
                });

            return HttpResponseHelper::make()
                ->successfulResponse("Estadísticas obtenidas correctamente", $stats)
                ->send();

        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Error al obtener estadísticas: " . $e->getMessage())
                ->send();
        }
    }

}
