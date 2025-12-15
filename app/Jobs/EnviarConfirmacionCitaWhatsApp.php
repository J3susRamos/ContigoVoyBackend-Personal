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

class EnviarConfirmacionCitaWhatsApp
{
    use Dispatchable;

    public Cita $cita;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function handle(WhatsAppService $whatsappService): void
    {
        // Nos aseguramos de tener relaciones cargadas
        $this->cita->loadMissing(['paciente', 'prepaciente', 'psicologo.users']);

        // ================= PACIENTE =================
        $nombrePaciente = $this->cita->prepaciente->nombre
            ?? $this->cita->paciente->nombre;

        $phonePaciente = $this->cita->prepaciente->celular
            ?? $this->cita->paciente->celular;

        // PSICÓLOGO - nombre completo
        $nombrePsicologo = 'su psicólogo asignado';
        if ($this->cita->psicologo && $this->cita->psicologo->users) {
            $nombrePsicologo =
                $this->cita->psicologo->users->name . ' ' .
                $this->cita->psicologo->users->apellido;
        }

        // Formatos requeridos
        $fechaFormateada = Carbon::parse($this->cita->fecha_cita)->format('Y-m-d'); // para el validador Node
        $horaFormateada = Carbon::parse($this->cita->hora_cita)->format('H:i');    // para el validador Node
        $fechaMostrar = Carbon::parse($this->cita->fecha_cita)->format('d/m/Y'); // para mensajes de texto

        // ===== 1) CONFIRMACIÓN AL PACIENTE (lo que ya tenías) =====
        try {
            if ($phonePaciente) {
                $whatsappService->sendConfirmationMessage(
                    $phonePaciente,
                    $nombrePsicologo,
                    $fechaFormateada,
                    $horaFormateada,
                    $nombrePaciente   // nombre paciente como 5to parámetro
                );
            }
        } catch (\Throwable $th) {
            Log::error('Error al enviar confirmación de cita por WhatsApp al paciente', [
                'cita_id' => $this->cita->idCita ?? null,
                'telefono' => $phonePaciente ?? null,
                'error' => $th->getMessage(),
            ]);
        }

        // ===== 2) NUEVO: CONFIRMACIÓN AL PSICÓLOGO =====
        try {
            if ($this->cita->psicologo && $this->cita->psicologo->celular) {
                $telefonoPsicologo = preg_replace(
                    '/\s+/',
                    '',
                    $this->cita->psicologo->celular
                );

                $mensajePsicologo =
                    "Hola {$nombrePsicologo}, se ha registrado una nueva cita.\n\n" .
                    "👤 Paciente: {$nombrePaciente}\n" .
                    "📅 Fecha: {$fechaMostrar}\n" .
                    "⏰ Hora: {$horaFormateada}\n\n" .
                    "Puedes revisar más detalles en tu panel de Contigo Voy.";

                $whatsappService->sendTextMessage($telefonoPsicologo, $mensajePsicologo);
            }
        } catch (\Throwable $th) {
            Log::error('Error al enviar confirmación de cita por WhatsApp al psicólogo', [
                'cita_id' => $this->cita->idCita ?? null,
                'telefono' => $this->cita->psicologo->celular ?? null,
                'error' => $th->getMessage(),
            ]);
        }
    }
}
