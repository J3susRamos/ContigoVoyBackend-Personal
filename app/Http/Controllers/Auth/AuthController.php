<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\PostAuth\PostAuth;
use App\Models\Psicologo;
use App\Traits\HttpResponseHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login(PostAuth $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse('El correo electrónico no está registrado.');

            }
            if (!Hash::check($request->password, $user->password)) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse('La contraseña es incorrecta.');

            }
            $token = $user->createToken('token')->plainTextToken;
            $responseData = [
                'token' => $token,
                'nombre' => $user->name,
                'apellido' => $user->apellido,
                'email' => $user->email,
                'id' => $user->user_id,
                'rol' => $user->rol,
                'imagen'=>$user->imagen
            ];

            if ($user->rol === 'PSICOLOGO') {
                $psicologo = Psicologo::where('user_id', $user->user_id)->first();

                if ($psicologo) {
                    $responseData['idpsicologo'] = $psicologo->idPsicologo; // Opcional: mantenerlo también en otro campo si quieres
                } else {
                    Log::warning("Usuario con rol PSICOLOGO no tiene registro en tabla psicologos. User ID: " . $user->id);
                }
            }

            return HttpResponseHelper::make()
                ->successfulResponse('Inicio de sesión exitoso.', $responseData)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()

                ->internalErrorResponse(
                    'Hubo un problema al procesar la solicitud.' .
                        $e->getMessage()
                )
                ->send();
        }
    }

    public function logout(PostAuth $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->tokens()->delete();

            return HttpResponseHelper::make()
                ->successfulResponse('Cierre de sesion exitoso.')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Hubo un problema al procesar la solicitud. Por favor, intente nuevamente.')
                ->send();
        }
    }
}
