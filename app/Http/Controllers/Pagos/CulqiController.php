<?php

namespace App\Http\Controllers\Pagos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Culqi\Culqi;

class CulqiController extends Controller
{
    public function crearCargo(Request $request)
{
    $culqi = new Culqi([
        'api_key' => config('services.culqi.secret'),
    ]);

    $cargo = $culqi->Charges->create([
    "amount" => $request->amount,
    "currency_code" => "PEN",
    "email" => "bersdecker@gmail.com",
    "source_id" => $request->token,
    "description" => "Consulta psicológica",
    ]);

    // CONVERTIR SI VIENE COMO STRING
    if (is_string($cargo)) {
    $cargo = json_decode($cargo);
    }

    // SI CULQI DEVUELVE ERROR
    if (isset($cargo->object) && $cargo->object === 'error') {
        return response()->json([
            'success' => false,
            'message' => $cargo->merchant_message ?? 'Error en el pago',
            'data' => $cargo
        ], 400);
    }

    // PAGO REALMENTE EXITOSO
    return response()->json([
        'success' => true,
        'message' => 'Pago realizado correctamente',
        'data' => $cargo
    ]);
}

}
