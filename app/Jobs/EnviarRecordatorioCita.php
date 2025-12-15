<?php

namespace App\Jobs;

use App\Models\Cita;
use App\Mail\CitaReminderMail;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\GoogleCalendarService; // (comentado en el cuerpo, pero lo dejamos por si luego lo usas)

class EnviarRecordatorioCita implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Cita $cita;
    public WhatsAppService $whatsappService;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function handle(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;

        // Aseguramos relaciones
        $this->cita->loadMissing(['paciente', 'prepaciente', 'psicologo.users']);

        $nombre = $this->cita->prepaciente->nombre ?? $this->cita->paciente->nombre;
        $email = $this->cita->prepaciente->correo ?? $this->cita->paciente->correo;
        $phone = $this->cita->prepaciente->celular ?? $this->cita->paciente->celular;

        $fecha = $this->cita->fecha_cita;
        $hora = $this->cita->hora_cita;

        //Enviar correo con el enlace
        try {
            Mail::to($email)->send(new CitaReminderMail($nombre, $fecha, $hora, $this->cita->jitsi_url));
        } catch (\Throwable $th) {
            Log::error('Error al enviar correo de recordatorio de cita', [
                'cita_id' => $this->cita->idCita ?? null,
                'email' => $email ?? null,
                'error' => $th->getMessage(),
            ]);
        }

        $message = "Hola $nombre, recuerda tu cita:\nFecha: $fecha\nHora: $hora";


        try {
            if ($phone) {
                $this->whatsappService->sendTextMessage($phone, $message);
            }
        } catch (\Throwable $th) {
            Log::error('Error al enviar recordatorio de cita por WhatsApp al paciente', [
                'cita_id' => $this->cita->idCita ?? null,
                'telefono' => $phone ?? null,
                'error' => $th->getMessage(),
            ]);
        }

        // Enviar WhatsApp
        $message = "Hola $nombre, recuerda tu cita:\nFecha: $fecha\nHora: $hora";


        try {
            if ($phone) {
                $this->whatsappService->sendTextMessage($phone, $message);
            }
        } catch (\Throwable $th) {
            Log::error('Error al enviar recordatorio de cita por WhatsApp al paciente', [
                'cita_id' => $this->cita->idCita ?? null,
                'telefono' => $phone ?? null,
                'error' => $th->getMessage(),
            ]);
        }

        // ========== 2) NUEVO: RECORDATORIO AL PSICÓLOGO ==========
        try {
            if ($this->cita->psicologo && $this->cita->psicologo->celular) {
                $telefonoPsicologo = preg_replace(
                    '/\s+/',
                    '',
                    $this->cita->psicologo->celular
                );

                $nombrePsicologo = $this->cita->psicologo->users
                    ? $this->cita->psicologo->users->name . ' ' . $this->cita->psicologo->users->apellido
                    : 'Psicólogo/a';

                $mensajePsicologo =
                    "Hola {$nombrePsicologo}, este es un recordatorio de tu cita:\n\n" .
                    "👤 Paciente: {$nombre}\n" .
                    "📅 Fecha: {$fecha}\n" .
                    "⏰ Hora: {$hora}";



                $this->whatsappService->sendTextMessage($telefonoPsicologo, $mensajePsicologo);
            }
        } catch (\Throwable $th) {
            Log::error('Error al enviar recordatorio de cita por WhatsApp al psicólogo', [
                'cita_id' => $this->cita->idCita ?? null,
                'telefono' => $this->cita->psicologo->celular ?? null,
                'error' => $th->getMessage(),
            ]);
        }
    }
}
