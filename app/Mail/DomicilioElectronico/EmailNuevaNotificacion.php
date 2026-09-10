<?php

namespace App\Mail\DomicilioElectronico;

use App\Contracts\LogsEmailPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailNuevaNotificacion extends Mailable implements LogsEmailPayload
{
    use Queueable, SerializesModels;

    public function __construct(public string $loginUrl)
    {
        $this->subject('Nueva notificación electrónica - Municipalidad de Cutral Co');
    }

    public function build()
    {
        return $this->view('emails.domicilio-electronico.nueva-notificacion', [
            'loginUrl' => $this->loginUrl,
        ]);
    }

    public function getEmailTemplateKey(): string
    {
        return 'domicilio-electronico.nueva-notificacion';
    }

    public function getEmailLogPayload(): array
    {
        return [
            'action' => 'new-notification-courtesy-notice',
            'flow' => 'domicilio-electronico',
            'login_url' => $this->loginUrl,
        ];
    }

    public function getEmailLogMeta(): array
    {
        return [
            'module' => 'domicilio-electronico',
            'template_group' => 'domicilio-electronico',
            'purpose' => 'Aviso de cortesía de nueva notificación electrónica.',
        ];
    }
}
