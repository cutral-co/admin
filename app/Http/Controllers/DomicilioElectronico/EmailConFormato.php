<?php

namespace App\Http\Controllers\DomicilioElectronico;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailConFormato extends Mailable
{
    use Queueable, SerializesModels;
    public $url;
    public $email;
    public $subject;
    public $mensaje;
    public $token;
    public $urlCustom;
    public $textoBoton;

    public function __construct($email, $subject, $mensaje, $token, $urlCustom, $textoBoton)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->mensaje = $mensaje;
        $this->token = $token;
        $this->urlCustom = $urlCustom;
        $this->textoBoton = $textoBoton;
    }

    public function build()
    {
        if ($this->token) {
            $this->url = env('MIMUNI_URL') . "#/domicilio_electronico_verification/{$this->email}/{$this->token}";
        } else {
            $this->url = env('MIMUNI_URL') . "#/";
        }

        if ($this->urlCustom) {
            $this->url = $this->urlCustom;
        }

        $mensajeLimpio = clean_html_basic($this->mensaje);


        return $this->markdown('Email.email_con_formato')->with([
            'url' => $this->url,
            'email' => $this->email,
            'mensaje' => $mensajeLimpio,
            'token' => $this->token,
            'textoBoton' => $this->textoBoton
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'Email.email_con_formato'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
