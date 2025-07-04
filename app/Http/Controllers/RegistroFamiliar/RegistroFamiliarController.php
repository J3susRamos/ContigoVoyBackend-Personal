<?php

namespace App\Http\Controllers\RegistroFamiliar;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRegistroFamiliar\PostRegistroFamiliar;
use App\Models\RegistroFamiliar;
use App\Traits\HttpResponseHelper;
use Illuminate\Http\JsonResponse;

class RegistroFamiliarController extends Controller
{
    public function createRegistro(PostRegistroFamiliar $request, int $id): JsonResponse
    {
        try {
            $registroFamiliarData = $request->validated();
            $registroFamiliarData['idPaciente'] = $id;
            RegistroFamiliar::create($registroFamiliarData);

            return HttpResponseHelper::make()
                ->successfulResponse('Registro familiar creado correctamente.')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud: ' . $e->getMessage())
                ->send();
        }
    }

    public function showRegistro(int $id): JsonResponse
    {
        try {
            $registroFamiliar = RegistroFamiliar::with('paciente:idPaciente,nombre,apellido,codigo')
                ->where('idPaciente', $id)
                ->first();

            if (!$registroFamiliar) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un registro familiar para este paciente.')
                    ->send();
            }

            return HttpResponseHelper::make()
                ->successfulResponse('Registro familiar obtenido correctamente.', $registroFamiliar->toArray())
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud: ' . $e->getMessage())
                ->send();
        }
    }

    public function updateRegistro(PostRegistroFamiliar $request, int $id): JsonResponse
    {
        try {
            $registroFamiliar = RegistroFamiliar::where('idPaciente', $id)->first();

            if (!$registroFamiliar) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un registro familiar para este paciente.')
                    ->send();
            }

            $registroFamiliar->update($request->validated());

            return HttpResponseHelper::make()
                ->successfulResponse('Registro familiar actualizado correctamente.')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al actualizar el registro familiar: ' . $e->getMessage())
                ->send();
        }
    }

    public function destroyRegistro(int $id): JsonResponse
    {
        try {
            $registroFamiliar = RegistroFamiliar::where('idPaciente', $id)->first();

            if (!$registroFamiliar) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un registro familiar para este paciente.')
                    ->send();
            }

            $registroFamiliar->delete();

            return HttpResponseHelper::make()
                ->successfulResponse('Registro familiar eliminado correctamente.')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al eliminar el registro familiar: ' . $e->getMessage())
                ->send();
        }
    }
}
