<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PrePacienteCreado;
use App\Mail\ConfirmacionPrePaciente;
use App\Models\Cita;

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

            // Obtener el jitsi_url desde la base de datos usando la cita (si existe)
            $jitsi_url = null;
            if ($this->idCita) {
                $cita = Cita::find($this->idCita);
                $jitsi_url = $cita ? $cita->jitsi_url : null;
            }

            // Enviar correos
            $adminEmail = config('emails.admin_address', 'contigovoyproject@gmail.com');

            Mail::to($adminEmail)->send(new PrePacienteCreado($this->datos));

            Mail::to($this->prePaciente->correo)->send(
                new ConfirmacionPrePaciente([
                    'nombre' => $this->prePaciente->nombre,
                    'fecha' => $this->fecha,
                    'hora' => $this->hora,
                    'psicologo' => $this->nombrePsicologo,
                ], $jitsi_url)
            );

            Log::info("Correos enviados correctamente (admin y paciente)");

            // ✅ Importante: NO enviar WhatsApp desde este Job
            Log::info("WhatsApp omitido en EnviarNotificacionesPrePaciente (solo correos)");

            Log::info('=== FIN: EnviarNotificacionesPrePaciente ===');
        } catch (\Exception $e) {
            Log::error('❌❌❌ ERROR GENERAL en EnviarNotificacionesPrePaciente: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== FIN: EnviarNotificacionesPrePaciente (ERROR) ===');
        }
    }
}
