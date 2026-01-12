<?php

namespace App\Http\Controllers\Pagos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CulqiController extends Controller
{
    public function crearOrdenQR(Request $request)
    {
        try {
            Log::info('📥 Datos recibidos:', $request->all());

            $amount = $request->amount;
            $description = $request->description;

            if (!$amount || !$description) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan parámetros: amount y description son requeridos'
                ], 400);
            }

            Log::info('🔑 Usando clave:', [
                'key' => substr(env('CULQI_PRIVATE_KEY'), 0, 10) . '...'
            ]);

        
            $response = Http::withOptions([
                    'verify' => false, // ⚠️ SOLO PARA LOCAL
                ])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('CULQI_PRIVATE_KEY'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.culqi.com/v2/orders', [
                    'amount' => (int) $amount, // 👈 Culqi exige entero
                    'currency_code' => 'PEN',
                    'description' => $description,
                    'order_number' => 'orden-' . time() . '-' . rand(1000, 9999),
                    'client_details' => [
                        'first_name' => 'Cliente',
                        'last_name' => 'Contigo Voy',
                        'email' => 'cliente@test.com',
                        'phone_number' => '999999999',
                    ],
                    'expiration_date' => time() + 86400,
                    'confirm' => false,
                ]);

            $data = $response->json();
            Log::info('📦 Respuesta de Culqi:', $data ?? []);

            if (!$response->successful()) {
                Log::error('❌ Error de Culqi:', $data ?? []);
                return response()->json([
                    'success' => false,
                    'message' => $data['user_message'] ?? 'Error al crear orden QR',
                    'details' => $data,
                ], 400);
            }

            return response()->json([
                'success' => true,
                'qr' => $data['qr_code'] ?? null,
                'orderId' => $data['id'] ?? null,
                'order' => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('💥 Error en crearOrdenQR: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
