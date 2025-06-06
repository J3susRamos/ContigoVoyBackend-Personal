<?php

namespace App\Http\Controllers\RespuestasBlog;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Respuesta;
use App\Traits\HttpResponseHelper;
use App\Http\Requests\PostRespuestaComentario\PostRespuestaComentario;
use Exception;
use Illuminate\Http\JsonResponse;

class RespuestaComentarioController extends Controller
{
    public function createRespuesta(PostRespuestaComentario $request): JsonResponse
    {
        try {
            $data = $request->validated();

            Respuesta::create($data);

            return HttpResponseHelper::make()
                ->successfulResponse('Respuesta creada correctamente')
                ->send();

        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function showRespuestasByComentario(int $idComentario): JsonResponse
    {
        try {
            $respuestas = Respuesta::where('idComentario', $idComentario)->get();

            return HttpResponseHelper::make()
                ->successfulResponse('Respuestas obtenidas correctamente', $respuestas)
                ->send();

        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function destroyRespuesta(int $id): JsonResponse
    {
        try {
            $respuesta = Respuesta::where('idRespuesta', $id)->firstOrFail();
            $respuesta->delete();

            return HttpResponseHelper::make()
                ->successfulResponse('Respuesta eliminada correctamente')
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al eliminar la respuesta: ' . $e->getMessage())
                ->send();
        }
    }
}
