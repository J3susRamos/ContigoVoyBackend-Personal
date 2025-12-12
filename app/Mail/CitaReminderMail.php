<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CitaReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $fecha;
    public $hora;
    public $jitsi_url;

    public function __construct($nombre, $fecha, $hora, $jitsi_url = null)
    {
        $this->nombre = $nombre;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->jitsi_url = $jitsi_url;
    }

    public function build()
    {
        return $this
            ->subject("Recordatorio de tu cita")
            ->view('emails.cita_reminder_plain')
            ->with([
                'nombre' => $this->nombre,
                'fecha' => $this->fecha,
                'hora' => $this->hora,
                'jitsi_url' => $this->jitsi_url,
            ]);
    }
}
