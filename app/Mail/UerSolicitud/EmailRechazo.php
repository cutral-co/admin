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
    public function __construct(public string $motivo)
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
        return $this->view('emails.user-solicitud.rechazo', [
            'motivo' => $this->motivo,
        ]);
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
            'motivo_rechazo' => $this->motivo,
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
