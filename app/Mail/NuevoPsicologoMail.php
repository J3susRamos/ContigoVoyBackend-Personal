<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoPsicologoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombreCompleto;
    public $especialidades;
    public $usuario;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct($nombreCompleto, $especialidades, $usuario, $password)
    {
        $this->nombreCompleto = $nombreCompleto;
        $this->especialidades = $especialidades;
        $this->usuario = $usuario;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido Psicologo',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nuevo_psicologo',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
