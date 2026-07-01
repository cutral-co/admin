<?php

namespace App\Mail\UerSolicitud;

use App\Contracts\LogsEmailPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailPostConfirmacion extends Mailable implements LogsEmailPayload
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->subject('Correo electrónico confirmado');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.user-solicitud.post-confirmacion');
    }

    public function getEmailTemplateKey(): string
    {
        return 'user-registration.post-confirmacion';
    }

    public function getEmailLogPayload(): array
    {
        return [
            'action' => 'post-email-confirmation',
            'flow' => 'user-registration',
        ];
    }

    public function getEmailLogMeta(): array
    {
        return [
            'module' => 'user-registration',
            'template_group' => 'user-solicitud',
        ];
    }
}
