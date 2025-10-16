<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostPersonal\PostPersonal;
use App\Traits\HttpResponseHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Personal;
use App\Models\PersonalPermission;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;



class PersonalController extends Controller
{
    public function createPersonal(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $userData = $request->only([
                "name",
                "apellido",
                "email",
                "password",
                "fecha_nacimiento",
                "imagen",
                "rol",
            ]);

            // Encriptar password
            $userData["password"] = Hash::make($request->password);

            // Fecha en formato correcto
            $userData["fecha_nacimiento"] = Carbon::parse(
                $userData["fecha_nacimiento"],
            )->format("Y-m-d");

            // Crear usuario
            $user = User::create($userData);

            // Guardar permisos
            if (
                $request->has("permissions") &&
                is_array($request->permissions)
            ) {
                foreach ($request->permissions as $url_id) {
                    // Obtener el nombre de la URL correspondiente al ID
                    $url = \App\Models\Urls::find($url_id);

                    if ($url) {
                        PersonalPermission::create([
                            "id_user" => $user->user_id,
                            "id_urls" => $url_id,
                            "name_permission" => $url->name,
                        ]);
                    }
                }
            }

            DB::commit();

            return HttpResponseHelper::make()
                ->successfulResponse("Personal creado correctamente", [
                    "user_id" => $user->user_id,
                ])
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud. " .
                        $e->getMessage(),
                )
                ->send();
        }
    }

    public function getPersonalWithPermissions(int $user_id): JsonResponse
    {
        try {
            $personal = Personal::find($user_id);

            if (!$personal) {
                return HttpResponseHelper::make()
                    ->notFoundResponse(
                        "El usuario con id {$user_id} no existe.",
                    )
                    ->send();
            }
            $permissions = PersonalPermission::where("id_user", $user_id)->get([
                "id",
                "name_permission",
                "id_user",
            ]);

            return HttpResponseHelper::make()
                ->successfulResponse(
                    "Permisos obtenidos correctamente",
                    $permissions,
                )
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un error al obtener los permisos. " .
                        $e->getMessage(),
                )
                ->send();
        }
    }


    //Agregado M.
    public function getPermissionsByEmail($email)
    {
        try {
            // Usar DB::table directamente
            $user = DB::table('users')->where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            // **ENCONTRAR EL ID CORRECTO**
            $userId = $user->id ?? $user->ID ?? $user->Id ?? $user->user_id ?? $user->UserID ?? $user->usuario_id ?? null;

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: No se pudo identificar el ID del usuario',
                ], 400);
            }

            // Obtener permisos desde personal_permissions
            $permissions = DB::table('personal_permissions')
                ->where('id_user', $userId)
                ->pluck('name_permission')
                ->toArray();

            return response()->json([
                'success' => true,
                'result' => [
                    'id' => $userId,
                    'name' => $user->name ?? $user->Name ?? $user->nombre ?? '',
                    'email' => $user->email ?? $user->Email ?? '',
                    'rol' => $user->rol ?? $user->Rol ?? $user->role ?? null,
                    'permissions' => $permissions,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos'
            ], 500);
        }
    }

    public function updatePermissionsByEmail(Request $request)
    {
        try {

            // Validación básica
            if (!$request->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email requerido',
                ], 400);
            }

            // Buscar usuario
            $user = DB::table('users')->where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado: ' . $request->email,
                ], 404);
            }

            // Obtener ID del usuario
            $userId = $user->id ?? $user->ID ?? $user->Id ?? $user->user_id ?? $user->UserID ?? $user->usuario_id ?? null;

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo identificar el ID del usuario',
                ], 400);
            }

            // ✅ Obtener permisos existentes del usuario
            $existingPermissions = DB::table('personal_permissions')
                ->where('id_user', $userId)
                ->pluck('name_permission')
                ->toArray();

            // ✅ VERIFICAR SI HAY DUPLICADOS
            $duplicatePermissions = array_intersect($request->permissions, $existingPermissions);

            if (!empty($duplicatePermissions)) {

                return response()->json([
                    'success' => false,
                    'message' => 'El usuario ya cuenta con algunos permisos seleccionados',
                    'error_type' => 'duplicate_permissions',
                    'duplicate_permissions' => array_values($duplicatePermissions), // ✅ Lista de duplicados
                    'existing_permissions' => $existingPermissions, // ✅ Todos los permisos existentes
                    'user_info' => [
                        'id' => $userId,
                        'name' => $user->name ?? $user->Name ?? $user->nombre ?? '',
                        'email' => $user->email ?? $user->Email ?? '',
                    ]
                ], 409); // ✅ 409 Conflict - indica que hay duplicados
            }

            // ✅ SOLO SI NO HAY DUPLICADOS - Insertar todos los permisos
            $insertedCount = 0;

            if (!empty($request->permissions) && is_array($request->permissions)) {
                $permissionsToInsert = [];

                foreach ($request->permissions as $permissionName) {
                    // Buscar ID de la URL
                    $urlId = null;
                    $url = DB::table('urls')->where('name', $permissionName)->first();
                    if ($url) {
                        $urlId = $url->id ?? $url->ID ?? $url->Id ?? null;
                    }

                    $permissionsToInsert[] = [
                        'id_urls' => $urlId,
                        'name_permission' => $permissionName,
                        'id_user' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($permissionsToInsert)) {
                    DB::table('personal_permissions')->insert($permissionsToInsert);
                    $insertedCount = count($permissionsToInsert);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Permisos agregados correctamente',
                'user_id' => $userId,
                'permissions_added' => $insertedCount,
                'permissions_list' => $request->permissions
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    //agregado recien M.
    public function removePermissionsByEmail(Request $request)
    {
        try {

            // Validación
            if (!$request->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email requerido',
                ], 400);
            }

            if (empty($request->permissions) || !is_array($request->permissions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lista de permisos a eliminar requerida',
                ], 400);
            }

            // Buscar usuario
            $user = DB::table('users')->where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado: ' . $request->email,
                ], 404);
            }

            // Encontrar el ID correcto
            $userId = $user->id ?? $user->ID ?? $user->Id ?? $user->user_id ?? $user->UserID ?? $user->usuario_id ?? null;

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: No se pudo identificar el ID del usuario',
                ], 400);
            }

            // ELIMINAR solo los permisos específicos (no todos)
            $deletedCount = DB::table('personal_permissions')
                ->where('id_user', $userId)
                ->whereIn('name_permission', $request->permissions)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permisos eliminados correctamente',
                'user_id' => $userId,
                'permissions_removed' => $deletedCount,
                'removed_permissions' => $request->permissions
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
