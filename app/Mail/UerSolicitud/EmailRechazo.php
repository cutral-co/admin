<?php

namespace App\Mail\UerSolicitud;

use App\Contracts\LogsEmailPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailRechazo extends Mailable implements LogsEmailPayload
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->subject('Se rechazo la solicitud');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.user-solicitud.rechazo');
    }

    public function getEmailTemplateKey(): string
    {
        return 'user-registration.rechazo';
    }

    public function getEmailLogPayload(): array
    {
        return [
            'action' => 'registration-rejected',
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
