<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredencialesPacienteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $email;
    public $password;
    public $loginURL;

    public function __construct($nombre, $email, $password)
    {
        $this->nombre = $nombre;
        $this->email = $email;
        $this->password = $password;
        $this->loginURL = env('LOGIN_URL');
    }

    public function build()
    {
        return $this->subject('Tus credenciales de acceso')
                    ->view('emails.credenciales-paciente');
    }
}