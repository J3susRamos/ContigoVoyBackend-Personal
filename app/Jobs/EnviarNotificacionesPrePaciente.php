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
use Carbon\Carbon;

class EnviarNotificacionesPrePaciente implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $prePaciente;
    public $datos;
    public $fecha;
    public $hora;
    public $nombrePsicologo;
    public $meet_link;

    public function __construct($prePaciente, $datos, $fecha, $hora, $nombrePsicologo, $meet_link = null)
    {
        $this->prePaciente = $prePaciente;
        $this->datos = $datos;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->nombrePsicologo = $nombrePsicologo;
        $this->meet_link = $datos['meet_link'] ?? $meet_link ?? null;
    }

    public function handle(): void
    {
        try {

            Log::info('=== INICIO: EnviarNotificacionesPrePaciente ===');

            // Enviar correos
            $adminEmail = config('emails.admin_address', 'contigovoyproject@gmail.com');
            Log::info("Enviando correo al admin: $adminEmail");


            Log::info('Datos enviados al Mailable:', $this->datos);

            Mail::to($adminEmail)->send(new PrePacienteCreado($this->datos));
            Log::info("Correo enviado al admin correctamente");


            $correoPaciente = $this->prePaciente->correo;
            Log::info("Enviando correo al paciente: $correoPaciente");

            Mail::to($this->prePaciente->correo)->send(
                new ConfirmacionPrePaciente([
                    'nombre' => $this->prePaciente->nombre,
                    'fecha' => $this->fecha,
                    'hora' => $this->hora,
                    'psicologo' => $this->nombrePsicologo,
                    'meet_link' => $this->meet_link,
                ])
            );

            Log::info("Correo enviado al paciente correctamente");

            // WhatsApp
            $mensaje = "👋 ¡Hola {$this->prePaciente->nombre}!

Tu consulta **GRATIS** está lista 💜  
Nos vemos el {$this->fecha} a las {$this->hora}.

Ingresa al Meet aquí:  
💻 {$this->meet_link}

Cualquier duda, escríbenos 😊";


            $whatsappService = app(WhatsAppService::class);
            $whatsappService->sendTextMessage($this->prePaciente->celular, $mensaje);

            Log::info('Datos recibidos por el Job', [
                'meet_link' => $this->meet_link,
                'datos_meet_link' => $this->datos['meet_link'] ?? 'NO EXISTE',
            ]);
        } catch (\Exception $e) {
            Log::error('❌❌❌ ERROR GENERAL en envío de notificaciones: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== FIN: EnviarNotificacionesPrePaciente (ERROR) ===');
        }



        Log::info('--- Fin del Job EnviarNotificacionesPrePaciente ---');
    }
}
