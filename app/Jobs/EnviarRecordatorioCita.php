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

use App\Services\GoogleCalendarService;


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

        $nombre = $this->cita->prepaciente->nombre ?? $this->cita->paciente->nombre;
        $email = $this->cita->prepaciente->correo ?? $this->cita->paciente->correo;
        $phone = $this->cita->prepaciente->celular ?? $this->cita->paciente->celular;

        $fecha = $this->cita->fecha_cita;
        $hora = $this->cita->hora_cita;
        $meet_link = $this->cita->psicologo->meet_link ?? null;

        /*
        // Crear evento en Google Calendar y generar enlace de Google Meet primero
        $meetLink = null;
        try {
            $event = $calendar->createEvent('primary', [
                'summary' => "Cita con $nombre",
                'description' => "Cita con el psicólogo asignado",
                'start' => "{$fecha}T{$hora}:00",
                'end' => "{$fecha}T" . date('H:i', strtotime("$hora +1 hour")) . ":00",
            ]);

            // Obtener el enlace de Google Meet del evento
            if ($event->getHangoutLink()) {
                $meetLink = $event->getHangoutLink();
            } elseif ($event->getConferenceData() && $event->getConferenceData()->getEntryPoints()) {
                $entryPoints = $event->getConferenceData()->getEntryPoints();
                if (!empty($entryPoints) && isset($entryPoints[0])) {
                    $meetLink = $entryPoints[0]->getUri();
                }
            }

            Log::info("Enlace de Google Meet generado: " . ($meetLink ?? 'No disponible'));
        } catch (\Throwable $th) {
            Log::error('Error creando evento en Google Calendar: ' . $th->getMessage());
        }
*/
         //Enviar correo con el enlace de Meet
        try {
            Mail::to($email)->send(new CitaReminderMail($nombre, $fecha, $hora, $meet_link));
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }

        // Enviar WhatsApp
        $message = "Hola $nombre, recuerda tu cita:\nFecha: $fecha\nHora: $hora";
        if ($meet_link) {
            $message .= "\n\nEnlace de Google Meet: $meet_link";
        }

        try {
            $this->whatsappService->sendTextMessage($phone, $message);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
