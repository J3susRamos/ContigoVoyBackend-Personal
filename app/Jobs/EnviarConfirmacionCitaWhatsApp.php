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
        try {
            $this->whatsappService = $whatsappService;

            // Obtener datos del paciente
            $nombre = $this->cita->prepaciente->nombre ?? $this->cita->paciente->nombre;
            $phone = $this->cita->prepaciente->celular ?? $this->cita->paciente->celular;

            if (!$phone) {
                Log::warning('No se pudo enviar WhatsApp - teléfono no disponible', [
                    'cita_id' => $this->cita->idCita
                ]);
                return;
            }

            // Obtener datos del psicólogo
            $nombrePsicologo = 'su psicólogo asignado';
            if ($this->cita->psicologo && $this->cita->psicologo->users) {
                $nombrePsicologo = $this->cita->psicologo->users->name . ' ' . $this->cita->psicologo->users->apellido;
            }

            // Formateos
            $fecha = Carbon::parse($this->cita->fecha_cita)->format('Y-m-d');
            $hora = Carbon::parse($this->cita->hora_cita)->format('H:i'); // 24h con minutos

            $this->whatsappService->sendAppointmentMessage(
                $phone,
                $nombrePsicologo,
                $fecha,
                $hora,
            );

        } catch (\Exception $e) {
            Log::error('Error enviando template cita_gratis por WhatsApp', [
                'cita_id' => $this->cita->idCita,
                'error' => $e->getMessage(),
                'telefono' => $phone ?? 'N/A'
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job template cita_gratis falló definitivamente', [
            'cita_id' => $this->cita->idCita,
            'error' => $exception->getMessage(),
            'template_usado' => 'cita_gratis'
        ]);
    }
}
