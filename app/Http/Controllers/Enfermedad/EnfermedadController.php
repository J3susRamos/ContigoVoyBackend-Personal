<?php

namespace App\Http\Controllers\Enfermedad;

use App\Http\Controllers\Controller;
use App\Models\Enfermedad;
use App\Traits\HttpResponseHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class EnfermedadController extends Controller
{
    public function showAll(): JsonResponse
    {
        try {
            $enfermedades = Enfermedad::all();

            return HttpResponseHelper::make()
                ->successfulResponse('Lista de especialidades obtenida correctamente', $enfermedades)
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener las especialidades: ' . $e->getMessage())
                ->send();
        }
    }
}
