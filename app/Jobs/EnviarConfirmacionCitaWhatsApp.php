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
    public WhatsAppService $whatsappService;

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

        // Obtener datos del paciente
        $this->whatsappService = $whatsappService;
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

        try {
            $this->whatsappService->sendAppointmentMessage($phone, $nombrePsicologo, $fechaFormateada, $horaFormateada);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());


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