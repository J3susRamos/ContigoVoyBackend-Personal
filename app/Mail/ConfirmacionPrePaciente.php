<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConfirmacionPrePaciente extends Mailable
{
    use Queueable, SerializesModels;

    public array $datos;

    /**
     * Create a new message instance.
     */
    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🌿¡Gracias por dar este paso hacia tu bienestar! 💜',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmacion_pre_paciente',
            with: [
                'datos' => $this->datos,
                'meet_link' => $this->datos['meet_link'] ?? null,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
