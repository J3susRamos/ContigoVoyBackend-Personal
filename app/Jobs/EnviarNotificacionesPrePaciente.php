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
use App\Services\GoogleCalendarService;
use App\Models\Cita;
use Carbon\Carbon;

class EnviarNotificacionesPrePaciente
{
    use Dispatchable;

    public $prePaciente;
    public $datos;
    public $fecha;
    public $hora;
    public $nombrePsicologo;
    public $idCita;

    public function __construct($prePaciente, $datos, $fecha, $hora, $nombrePsicologo, $idCita = null)
    {
        $this->prePaciente = $prePaciente;
        $this->datos = $datos;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->nombrePsicologo = $nombrePsicologo;
        $this->idCita = $idCita;
    }

    public function handle(): void
    {
        try {

            Log::info('=== INICIO: EnviarNotificacionesPrePaciente ===');

            // Obtener el jitsi_url desde la base de datos usando la cita
            $jitsi_url = null;
            if ($this->idCita) {
                $cita = Cita::find($this->idCita);
                $jitsi_url = $cita ? $cita->jitsi_url : null;

            }

            // Enviar correos
            $adminEmail = config('emails.admin_address', 'contigovoyproject@gmail.com');

            Mail::to($adminEmail)->send(new PrePacienteCreado($this->datos));

            $correoPaciente = $this->prePaciente->correo;

            Mail::to($this->prePaciente->correo)->send(
                new ConfirmacionPrePaciente([
                    'nombre' => $this->prePaciente->nombre,
                    'fecha' => $this->fecha,
                    'hora' => $this->hora,
                    'psicologo' => $this->nombrePsicologo,
                ], $jitsi_url)
            );

            Log::info("Correo enviado al paciente correctamente");

            // WhatsApp
            $mensaje = "👋 ¡Hola {$this->prePaciente->nombre}!

Tu consulta **GRATIS** está lista 💜
Nos vemos el {$this->fecha} a las {$this->hora}.

Ingresa a la reunion: {$jitsi_url}

Cualquier duda, escríbenos 😊";

            if ($this->prePaciente->celular) {
                $whatsappService = new WhatsAppService();
                $whatsappService->sendTextMessage($this->prePaciente->celular, $mensaje);
                Log::info("WhatsApp enviado correctamente a: {$this->prePaciente->celular}");
            }

        } catch (\Exception $e) {
            Log::error('❌❌❌ ERROR GENERAL en envío de notificaciones: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== FIN: EnviarNotificacionesPrePaciente (ERROR) ===');
        }

    }
}
