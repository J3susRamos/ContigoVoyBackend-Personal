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
                ->successfulResponse("Psicólogos obtenidos correctamente", [
                    "data" => $paginator,
                ])
                ->send();
    
                
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse("Error al obtener psicólogos: " . $e->getMessage())
                ->send();
        }
    }

}
