<?php

namespace App\Jobs;

use App\Models\Cita;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Bus\Dispatchable;
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

        // ✅ JITSI URL (viene de la cita)
        $jitsi_url = $this->cita->jitsi_url ?? null;

        // Formatos requeridos
        $fechaFormateada = Carbon::parse($this->cita->fecha_cita)->format('Y-m-d'); // para el validador Node
        $horaFormateada = Carbon::parse($this->cita->hora_cita)->format('H:i');    // para el validador Node
        $fechaMostrar = Carbon::parse($this->cita->fecha_cita)->format('d/m/Y'); // para mensajes

        // ===== 1) CONFIRMACIÓN AL PACIENTE =====
        try {
            if ($phonePaciente) {

                // Si tu método soporta el link como parámetro, úsalo:
                // $whatsappService->sendConfirmationMessage(
                //     $phonePaciente,
                //     $nombrePsicologo,
                //     $fechaFormateada,
                //     $horaFormateada,
                //     $nombrePaciente,
                //     $jitsi_url
                // );

                // ✅ Si NO soporta link (lo más probable), manda un texto adicional:
                $whatsappService->sendConfirmationMessage(
                    $phonePaciente,
                    $nombrePsicologo,
                    $fechaFormateada,
                    $horaFormateada,
                    $nombrePaciente,
                    $jitsi_url
                );

            }
        } catch (\Throwable $th) {
            Log::error('Error al enviar confirmación de cita por WhatsApp al paciente', [
                'cita_id' => $this->cita->idCita ?? null,
                'telefono' => $phonePaciente ?? null,
                'error' => $th->getMessage(),
            ]);
        }

        // ===== 2) CONFIRMACIÓN AL PSICÓLOGO =====
        try {
            if ($this->cita->psicologo && $this->cita->psicologo->celular) {
                $telefonoPsicologo = preg_replace('/\s+/', '', $this->cita->psicologo->celular);

                $mensajePsicologo =
                    "📢 Nueva cita agendada 📅\n\n" .
                    "Hola, {$nombrePsicologo} 👋\n" .
                    "Se ha registrado una nueva cita a través de la página web.\n\n" .
                    "📝 Detalles de la cita:\n" .
                    "✨ Paciente: {$nombrePaciente}\n" .
                    "📅 Fecha: {$fechaMostrar}\n" .
                    "⏰ Hora: {$horaFormateada}\n\n";

                // ✅ Agregar link si existe
                if ($jitsi_url) {
                    $mensajePsicologo .=
                        "🎥 Enlace a la reunión:\n" .
                        "👉 {$jitsi_url}\n\n";
                }

                $mensajePsicologo .=
                    "🔔 Acción requerida:\n" .
                    "Por favor, revisa tu agenda y confirma tu disponibilidad para esta cita.";

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
