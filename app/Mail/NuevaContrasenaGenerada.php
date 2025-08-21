<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevaContrasenaGenerada extends Mailable
{
    use Queueable, SerializesModels;

    public $password;
    public $loginURL;

    public function __construct($password)
    {
        $this->password = $password;
        $this->loginURL = env('LOGIN_URL');
    }

    public function build()
    {
        return $this->subject('Nueva Contrasena Generada')
                    ->view('emails.nueva_contrasena'); 
    }
}