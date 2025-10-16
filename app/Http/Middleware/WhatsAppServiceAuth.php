<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;

class WhatsAppServiceAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Verificar si el servicio WhatsApp está configurado
            $serviceUrl = config('services.whatsapp_service.base_url');
            $serviceToken = config('services.whatsapp_service.token');

            if (!$serviceUrl || !$serviceToken) {
                Log::error('WhatsApp Service not properly configured', [
                    'url_configured' => !empty($serviceUrl),
                    'token_configured' => !empty($serviceToken)
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'WhatsApp Service not configured',
                    'message' => 'El servicio de WhatsApp no está configurado correctamente'
                ], 503);
            }

            // Verificar si el servicio está disponible
            $whatsappService = app(WhatsAppService::class);
            $status = $whatsappService->getConnectionStatus();

            if (!$status['success']) {
                Log::warning('WhatsApp Service unavailable', [
                    'service_url' => $serviceUrl,
                    'status' => $status
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'WhatsApp Service unavailable',
                    'message' => 'El servicio de WhatsApp no está disponible en este momento'
                ], 503);
            }

            // Verificar si está conectado (para operaciones que requieren conexión activa)
            $requiresConnection = $this->requiresActiveConnection($request);
            if ($requiresConnection && !$whatsappService->isConnected()) {
                Log::warning('WhatsApp Service not connected', [
                    'endpoint' => $request->path(),
                    'method' => $request->method()
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'WhatsApp not connected',
                    'message' => 'WhatsApp no está conectado. Escanea el código QR para conectar.',
                    'qr_needed' => true
                ], 424); // 424 Failed Dependency
            }

            // Agregar información del servicio a la request
            $request->attributes->set('whatsapp_service_status', $status);
            $request->attributes->set('whatsapp_connected', $whatsappService->isConnected());

        } catch (\Exception $e) {
            Log::error('WhatsApp Service middleware error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Service error',
                'message' => 'Error interno del servicio de WhatsApp'
            ], 500);
        }

        return $next($request);
    }

    /**
     * Determina si el endpoint requiere una conexión activa de WhatsApp
     */
    private function requiresActiveConnection(Request $request): bool
    {
        $path = $request->path();
        $method = $request->method();

        // Endpoints que requieren conexión activa
        $requiresConnection = [
            // Envío de mensajes
            'POST:api/whatsapp/send-confirmation',
            'POST:api/whatsapp/send-reminder',
            'POST:api/whatsapp/send-interactive',

            // Obtener mensajes enviados
            'GET:api/whatsapp/sent-messages',
        ];

        $currentEndpoint = $method . ':' . $path;

        return in_array($currentEndpoint, $requiresConnection);
    }
}
