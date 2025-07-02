<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailMarketing extends Mailable
{
    use Queueable, SerializesModels;

    public $asunto;
    public $bloques;
    public $remitente;

    public function __construct($asunto, $bloques, $remitente)
    {
        $this->asunto = $asunto;
        $this->bloques = $bloques;
        $this->remitente = $remitente;
    }

    public function build()
    {
        return $this->from($this->remitente)
                    ->subject($this->asunto)
                    ->view('emails.plantilla_marketing') 
                    ->with(['bloques' => $this->bloques]);
    }

}
