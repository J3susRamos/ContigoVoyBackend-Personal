<?php

namespace App\Jobs;

use App\Models\Cita;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnviarConfirmacionCitaWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Cita $cita;
    public int $tries = 3;
    public int $backoff = 60; // Reintenta después de 1 minuto

    /**
     * Create a new job instance.
     */
    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsappService): void
    {
        try {
            // Obtener datos del paciente
            $nombre = $this->cita->prepaciente->nombre ?? $this->cita->paciente->nombre;
            $phone = $this->cita->prepaciente->celular ?? $this->cita->paciente->celular;
            
            // Obtener datos del psicólogo
            $nombrePsicologo = 'su psicólogo asignado';
            if ($this->cita->psicologo && $this->cita->psicologo->users) {
                $nombrePsicologo = $this->cita->psicologo->users->name . ' ' . $this->cita->psicologo->users->apellido;
            }

            // Formatear fecha y hora
            $fechaFormateada = Carbon::parse($this->cita->fecha_cita)->format('d/m/Y');
            $horaFormateada = Carbon::parse($this->cita->hora_cita)->format('H:i');

            Log::info('Enviando confirmación de cita por WhatsApp', [
                'cita_id' => $this->cita->idCita,
                'paciente' => $nombre,
                'telefono' => $phone,
                'psicologo' => $nombrePsicologo,
                'fecha' => $fechaFormateada,
                'hora' => $horaFormateada
            ]);

            // Verificar que el servicio WhatsApp esté conectado
            if (!$whatsappService->isConnected()) {
                Log::warning('Servicio WhatsApp no conectado, reintentando conexión');
                $whatsappService->forceReconnect();
                
                // Esperar un momento y verificar nuevamente
                sleep(2);
                if (!$whatsappService->isConnected()) {
                    throw new \Exception('Servicio WhatsApp no disponible después del reintento de conexión');
                }
            }

            // Crear mensaje de confirmación personalizado
            $mensaje = "🎉 ¡Hola {$nombre}!\n\n";
            $mensaje .= "✅ Tu cita ha sido confirmada exitosamente:\n\n";
            $mensaje .= "👨‍⚕️ **Psicólogo:** {$nombrePsicologo}\n";
            $mensaje .= "📅 **Fecha:** {$fechaFormateada}\n";
            $mensaje .= "⏰ **Hora:** {$horaFormateada}\n\n";
            $mensaje .= "📍 **Modalidad:** {$this->cita->motivo_Consulta}\n\n";
            $mensaje .= "💡 **Recordatorios importantes:**\n";
            $mensaje .= "• Llega 10 minutos antes de tu cita\n";
            $mensaje .= "• Trae una identificación válida\n";
            $mensaje .= "• Si necesitas reprogramar, contactanos con 24h de anticipación\n\n";
            $mensaje .= "📞 Para cualquier consulta, no dudes en contactarnos.\n\n";
            $mensaje .= "¡Te esperamos! 😊\n";
            $mensaje .= "*Centro Psicológico Contigo Voy*";

            // Intentar enviar usando el método de confirmación específico
            $response = $whatsappService->sendConfirmationMessage(
                $phone,
                $nombrePsicologo,
                $fechaFormateada,
                $horaFormateada
            );

            // Si el template no funciona, usar mensaje de texto personalizado
            if (!$response['success']) {
                Log::info('Template de confirmación falló, enviando mensaje personalizado', [
                    'error' => $response['error'] ?? 'Error desconocido'
                ]);
                
                $response = $whatsappService->sendTextMessage($phone, $mensaje);
            }

            if ($response['success']) {
                Log::info('Confirmación de cita enviada exitosamente por WhatsApp', [
                    'cita_id' => $this->cita->idCita,
                    'telefono' => $phone,
                    'message_id' => $response['message_id'] ?? null,
                    'response' => $response
                ]);
            } else {
                throw new \Exception('Error al enviar mensaje: ' . ($response['error'] ?? 'Error desconocido'));
            }

        } catch (\Exception $e) {
            Log::error('Error enviando confirmación de cita por WhatsApp', [
                'cita_id' => $this->cita->idCita,
                'error' => $e->getMessage(),
                'telefono' => $phone ?? 'N/A',
                'intento' => $this->attempts(),
                'trace' => $e->getTraceAsString()
            ]);

            // Si es el último intento, registrar el fallo definitivo
            if ($this->attempts() >= $this->tries) {
                Log::error('Fallo definitivo enviando confirmación WhatsApp después de todos los reintentos', [
                    'cita_id' => $this->cita->idCita,
                    'intentos_realizados' => $this->attempts()
                ]);
            }

            // Relanzar la excepción para que Laravel maneje los reintentos
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de confirmación WhatsApp falló definitivamente', [
            'cita_id' => $this->cita->idCita,
            'error' => $exception->getMessage(),
            'intentos_totales' => $this->tries
        ]);

        // Aquí podrías enviar una notificación al admin o registrar en una tabla de fallos
        // Por ejemplo, crear un registro en una tabla 'notification_failures'
    }
}