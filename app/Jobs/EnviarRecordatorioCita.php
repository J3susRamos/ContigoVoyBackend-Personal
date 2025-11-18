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

        // Enviar correo
        try {
            Mail::to($email)->send(new CitaReminderMail($nombre, $fecha, $hora));
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
        // Enviar WhatsApp
        $message = "Hola $nombre, recuerda tu cita:\nFecha: $fecha\nHora: $hora";

        try {
            $this->whatsappService->sendTextMessage($phone, $message);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
