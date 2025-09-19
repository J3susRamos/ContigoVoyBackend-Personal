<?php

namespace App\Http\Controllers\Especialidad;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostEspecialidad\PostEspecialidad;
use App\Models\Especialidad;
use App\Traits\HttpResponseHelper;
use Illuminate\Http\JsonResponse;

class EspecialidadController extends Controller
{
    public function createEspecialidad(PostEspecialidad $request): JsonResponse
    {
        try {
            // Verificar si ya existe una especialidad con el mismo nombre
            $exists = Especialidad::where('nombre', $request->nombre)->exists();

            if ($exists) {
                return HttpResponseHelper::make()
                    ->internalErrorResponse('La especialidad ya está registrada.') // Respuesta con error 409
                    ->send();
            }

            // Crear la especialidad si no existe
            $especialidad = Especialidad::create($request->all());

            return HttpResponseHelper::make()
                ->successfulResponse('Especialidad creada correctamente', [
                    'idEspecialidad' => $especialidad->idEspecialidad,
                    'nombre' => $especialidad->nombre
                ])
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function showAll(): JsonResponse
    {
        try {
            $especialidades = Especialidad::all();

            return HttpResponseHelper::make()
                ->successfulResponse('Lista de especialidades obtenida correctamente', $especialidades)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener las especialidades: ' . $e->getMessage())
                ->send();
        }
    }

    public function updateEspecialidad(PostEspecialidad $request, int $id): JsonResponse
    {
        try {
            $especialidad = Especialidad::findOrFail($id);
            $especialidad->update($request->all());

            return HttpResponseHelper::make()
                ->successfulResponse('Especialidad actualizada correctamente'   )
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al actualizar la especialidad: ' . $e->getMessage())
                ->send();
        }
    }

    public function destroyEspecialidad(int $id): JsonResponse
    {
        try {
            $especialidad = Especialidad::findOrFail($id);
            $especialidad->delete();

            return HttpResponseHelper::make()
                ->successfulResponse('Especialidad eliminada correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al eliminar la especialidad: ' . $e->getMessage())
                ->send();
        }
    }
}
