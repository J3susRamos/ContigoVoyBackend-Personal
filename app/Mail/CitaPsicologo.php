<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaPsicologo extends Mailable
{
    use Queueable, SerializesModels;

          public array $datos;
    public $jitsi_url;

    /**
     * Create a new message instance.
     */
    public function __construct(array $datos, $jitsi_url = null)
    {
        $this->datos = $datos;
        $this->jitsi_url = $jitsi_url;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
      return new Envelope(
            subject: '🌿¡Cita con el paciente! 💜',
        );
    }



    /**
     * Get the message content definition.
     */

    public function content(): Content
    {
        return new Content(
            view: 'emails.cita_psicologo',
            with: [
                'datos' => $this->datos,
                'jitsi_url' => $this->jitsi_url,
            ]
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
