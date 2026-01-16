<?php
namespace App\Http\Controllers\Pagos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CulqiController extends Controller
{
    // ========================================
    // MÉTODO QR (Yape / Plin) - MODO TEST
    // ========================================
    public function crearOrdenQR(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|integer|min:1',
                'description' => 'required|string',
            ]);

            $amount = $request->amount;
            $description = $request->description;

            Log::info('🔵 Iniciando pago QR', [
                'amount' => $amount,
                'description' => $description
            ]);

            // ✅ Para TEST: Crear charge directo sin order
            // En PRODUCCIÓN necesitarías crear primero un order
            $chargeResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('CULQI_PRIVATE_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.culqi.com/v2/charges', [
                'amount' => $amount,
                'currency_code' => 'PEN',
                'description' => $description,
                'email' => 'test@culqi.com',
                'payment_method' => [
                    'type' => 'yape',  // Puede ser 'yape' o 'plin'
                ],
            ]);

            if (!$chargeResponse->successful()) {
                Log::error('❌ Error en Culqi API', [
                    'status' => $chargeResponse->status(),
                    'response' => $chargeResponse->json()
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $chargeResponse->json(),
                ], 400);
            }

            $charge = $chargeResponse->json();

            Log::info('✅ QR generado exitosamente', [
                'charge_id' => $charge['id']
            ]);

            // El QR viene en payment_method->qr_code
            return response()->json([
                'success' => true,
                'qr' => $charge['payment_method']['qr_code'] ?? null,
                'charge_id' => $charge['id'],
                'status' => $charge['outcome']['type'] ?? 'pending',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Error de validación', ['errors' => $e->errors()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Exception en crearOrdenQR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ========================================
    // MÉTODO TARJETA (Culqi Checkout)
    // ========================================
    public function crearCargo(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'amount' => 'required|integer|min:1',
                'email' => 'required|email',
            ]);

            $amount = $request->amount;
            $token = $request->token;
            $email = $request->email;

            Log::info('🔵 Iniciando pago con tarjeta', [
                'amount' => $amount,
                'email' => $email,
                'token' => substr($token, 0, 20) . '...'
            ]);

            // Crear el cargo con el token de Culqi
            $chargeResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('CULQI_PRIVATE_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.culqi.com/v2/charges', [
                'amount' => $amount,
                'currency_code' => 'PEN',
                'email' => $email,
            ]);

            if (!$chargeResponse->successful()) {
                Log::error('❌ Error en cargo con tarjeta', [
                    'status' => $chargeResponse->status(),
                    'response' => $chargeResponse->json()
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $chargeResponse->json(),
                ], 400);
            }

            $charge = $chargeResponse->json();

            Log::info('✅ Cargo con tarjeta exitoso', [
                'charge_id' => $charge['id']
            ]);

            return response()->json([
                'success' => true,
                'charge' => $charge,
                'charge_id' => $charge['id'],
                'message' => 'Pago procesado exitosamente',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Error de validación', ['errors' => $e->errors()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Exception en crearCargo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ========================================
    // WEBHOOK (Para recibir notificaciones de Culqi)
    // ========================================
    public function webhook(Request $request)
    {
        try {
            Log::info('🔔 Webhook Culqi recibido', $request->all());

            // Aquí puedes procesar la notificación según el evento
            $event = $request->input('object');
            $chargeId = $request->input('data.id');

            // Ejemplo: actualizar el estado del pago en tu DB
            if ($event === 'charge.succeeded') {
                Log::info('✅ Pago confirmado', ['charge_id' => $chargeId]);
                // TODO: Actualizar estado en base de datos
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('❌ Error en webhook', [
                'message' => $e->getMessage()
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    // ========================================
    // MÉTODO OPCIONAL: Verificar estado de un pago
    // ========================================
    public function verificarPago($chargeId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('CULQI_PRIVATE_KEY'),
            ])->get("https://api.culqi.com/v2/charges/{$chargeId}");

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudo verificar el pago',
                ], 400);
            }

            $charge = $response->json();

            return response()->json([
                'success' => true,
                'status' => $charge['outcome']['type'],
                'charge' => $charge,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error verificando pago', [
                'charge_id' => $chargeId,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}