<?php

namespace App\Mail\UerSolicitud;

use App\Contracts\LogsEmailPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailAprobacion extends Mailable implements LogsEmailPayload
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $cuit,
        public string $password,
    ) {
        $this->subject('Adherido a la factura digital');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.user-solicitud.adherido', [
            'cuit' => $this->cuit,
            'password' => $this->password,
        ]);
    }

    public function getEmailTemplateKey(): string
    {
        return 'user-registration.aprobacion';
    }

    public function getEmailLogPayload(): array
    {
        return [
            'action' => 'registration-approved',
            'flow' => 'user-registration',
            'credentials' => [
                'cuit' => $this->cuit,
                'password' => $this->password,
            ],
        ];
    }

    public function getEmailLogMeta(): array
    {
        return [
            'module' => 'user-registration',
            'template_group' => 'user-solicitud',
            'note' => 'Preparado para registrar datos del usuario cuando el mailable los reciba.',
        ];
    }
}
