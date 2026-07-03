<?php

namespace App\Mail\UerSolicitud;

use App\Contracts\LogsEmailPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailDomicilioElectronicoMigracion extends Mailable implements LogsEmailPayload
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $cuit,
        public string $password,
        public string $termsUrl,
    ) {
        $this->subject('Nuevo acceso y domicilio electrónico');
    }

    public function build()
    {
        return $this->view('emails.user-solicitud.domicilio-electronico-migracion', [
            'cuit' => $this->cuit,
            'password' => $this->password,
            'termsUrl' => $this->termsUrl,
        ]);
    }

    public function getEmailTemplateKey(): string
    {
        return 'domicilio-electronico.migracion-credenciales';
    }

    public function getEmailLogPayload(): array
    {
        return [
            'action' => 'domicilio-electronico-migracion',
            'flow' => 'domicilio-electronico',
            'credentials' => [
                'cuit' => $this->cuit,
                'password' => $this->password,
            ],
            'terms_url' => $this->termsUrl,
        ];
    }

    public function getEmailLogMeta(): array
    {
        return [
            'module' => 'domicilio-electronico',
            'template_group' => 'user-solicitud',
            'purpose' => 'Envío masivo a usuarios legacy con nueva clave y aviso de domicilio electrónico.',
        ];
    }
}
