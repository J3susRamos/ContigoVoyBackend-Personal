<?php

namespace App\Http\Controllers\Estadisticas;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EstadisticasController extends Controller
{
    public function index(): JsonResponse
    {
        $data = [
            'usuarios' => 120,
            'pacientes' => 80,
            'psicologos' => 10,
            'citas' => 200,
        ];

        return response()->json($data);
    }
}
