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
            $userData["fecha_nacimiento"] = Carbon::createFromFormat(
                "d/m/Y",
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
}
