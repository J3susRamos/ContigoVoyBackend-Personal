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

    public function __construct($nombre, $fecha, $hora)
    {
        $this->nombre = $nombre;
        $this->fecha = $fecha;
        $this->hora = $hora;
    }

    public function build()
    {
        return $this
            ->subject("Recordatorio de tu cita")
            ->text('emails.cita_reminder_plain'); // texto plano
    }
}
