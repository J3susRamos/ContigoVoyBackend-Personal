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
    public $meet_link;

    public function __construct($nombre, $fecha, $hora, $meet_link = null)
    {
        $this->nombre = $nombre;
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->meet_link = $meet_link;
    }

    public function build()
    {
        return $this
            ->subject("Recordatorio de tu cita")
            ->view('emails.cita_reminder_plain') // texto plano
            ->with([
                'nombre' => $this->nombre,
                'fecha' => $this->fecha,
                'hora' => $this->hora,
                'meet_link' => $this->meet_link,
            ]);
    }
}
