<?php

namespace App\Jobs;

use App\Models\Cita;
use App\Mail\ConfirmacionPrePaciente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EnviarConfirmacionCitaCorreo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Cita $cita;

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
    public function handle(): void
    {
        try {
            // Obtener paciente (prepaciente o paciente normal)
            $paciente = $this->cita->prepaciente ?? $this->cita->paciente;

            if (!$paciente || !$paciente->correo) {
                Log::warning('No se pudo enviar correo - paciente o correo no disponible', [
                    'cita_id' => $this->cita->idCita ?? null,
                ]);
                return;
            }

            $nombre = $paciente->nombre;

            // Obtener datos del psicólogo
            $nombrePsicologo = 'su psicólogo asignado';
            if ($this->cita->psicologo && $this->cita->psicologo->users) {
                $nombrePsicologo = $this->cita->psicologo->users->name . ' ' . $this->cita->psicologo->users->apellido;
            }

            // Formatear fecha y hora
            $fecha = Carbon::parse($this->cita->fecha_cita)->format('Y-m-d');
            $hora = Carbon::parse($this->cita->hora_cita)->format('H:i');

            // Correo al admin (opcional)
            $adminEmail = config('emails.admin_address', 'contigovoyproject@gmail.com');
            Mail::to($adminEmail)->send(
                new ConfirmacionPrePaciente([
                    'nombre' => $nombre,
                    'fecha' => $fecha,
                    'hora' => $hora,
                    'psicologo' => $nombrePsicologo,
                ])
            );

            // Correo al paciente
            Mail::to($paciente->correo)->send(
                new ConfirmacionPrePaciente([
                    'nombre' => $nombre,
                    'fecha' => $fecha,
                    'hora' => $hora,
                    'psicologo' => $nombrePsicologo,
                ])
            );

        } catch (\Exception $e) {
            Log::error('Error enviando confirmación de cita por correo', [
                'cita_id' => $this->cita->idCita ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job EnviarConfirmacionCitaCorreo falló definitivamente', [
            'cita_id' => $this->cita->idCita ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
