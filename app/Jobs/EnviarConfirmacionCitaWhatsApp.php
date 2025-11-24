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

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function handle(WhatsAppService $whatsappService): void
    {
        // Paciente
        $nombre = $this->cita->prepaciente->nombre ?? $this->cita->paciente->nombre;
        $phone = $this->cita->prepaciente->celular ?? $this->cita->paciente->celular;

        // Psicólogo
        $nombrePsicologo = 'su psicólogo asignado';
        if ($this->cita->psicologo && $this->cita->psicologo->users) {
            $nombrePsicologo =
                $this->cita->psicologo->users->name . ' ' .
                $this->cita->psicologo->users->apellido;
        }

        // 👇 FORMATO QUE PIDE EL VALIDADOR DEL SERVICIO NODE
        $fechaFormateada = Carbon::parse($this->cita->fecha_cita)->format('Y-m-d'); // YYYY-MM-DD
        $horaFormateada = Carbon::parse($this->cita->hora_cita)->format('H:i');    // HH:MM

        try {
            $whatsappService->sendConfirmationMessage(
                $phone,
                $nombrePsicologo,
                $fechaFormateada,
                $horaFormateada,
                $nombre   // si ya añadiste el nombre como 5to parámetro
            );
        } catch (\Throwable $th) {
            Log::error('Error al enviar confirmación de cita por WhatsApp', [
                'cita_id' => $this->cita->idCita ?? null,
                'telefono' => $phone ?? null,
                'error' => $th->getMessage(),
            ]);
        }
    }

}
