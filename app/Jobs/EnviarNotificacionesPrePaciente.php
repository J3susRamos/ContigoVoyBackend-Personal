<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Mail\PrePacienteCreado;
use App\Mail\ConfirmacionPrePaciente;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarNotificacionesPrePaciente implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $prePaciente;
    public $datos;
    public $fecha;
    public $hora;
    public $nombrePsicologo;

    public function __construct($prePaciente, $datos, $fecha, $hora, $nombrePsicologo)
    {
        $this->prePaciente = $prePaciente;
        $this->datos = $datos;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->nombrePsicologo = $nombrePsicologo;
    }

    public function handle(): void
    {
        try {
            // Enviar correos
            $adminEmail = config('emails.admin_address', 'contigovoyproject@gmail.com');
            Mail::to($adminEmail)->send(new PrePacienteCreado($this->datos));

            Mail::to($this->prePaciente->correo)->send(
                new ConfirmacionPrePaciente([
                    'nombre' => $this->prePaciente->nombre,
                    'fecha' => $this->fecha,
                    'hora' => $this->hora,
                    'psicologo' => $this->nombrePsicologo,
                ])
            );

            // WhatsApp
            $mensaje = "¡Hola {$this->prePaciente->nombre}! 👋\n\n" .
                "✅ Tu primera cita GRATUITA ha sido confirmada:\n\n" .
                "📅 Fecha: {$this->fecha}\n" .
                "🕐 Hora: {$this->hora}\n" .
                "👨‍⚕️ Psicólogo: {$this->nombrePsicologo}\n\n" .
                "🎉 ¡Recuerda que tu primera consulta es GRATIS!\n\n" .
                "¡Te esperamos! 🌟";

            $whatsappService = app(WhatsAppService::class);
            $whatsappService->sendTextMessage($this->prePaciente->celular, $mensaje);
        } catch (\Exception $e) {
            Log::error('Error en envío de notificaciones: ' . $e->getMessage());
        }
    }
}
