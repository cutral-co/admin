<?php

namespace App\Mail\UerSolicitud;

use App\Contracts\LogsEmailPayload;
use App\Mail\UerSolicitud\Concerns\InteractsWithEmailLogs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailConfirmacion extends Mailable implements LogsEmailPayload
{
    use InteractsWithEmailLogs, Queueable, SerializesModels;

    public $link;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($link)
    {
        $this->link = $link;
        $this->subject('Confirmación de correo electrónico');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.user-solicitud.confirmacion');
    }

    public function getEmailTemplateKey(): string
    {
        return 'user-registration.confirmacion';
    }

    public function getEmailLogPayload(): array
    {
        $token = null;
        $path = null;

        $query = parse_url($this->link, PHP_URL_QUERY);
        $path = parse_url($this->link, PHP_URL_PATH);

        if ($query) {
            parse_str($query, $queryParams);
            $token = $queryParams['token'] ?? null;
        }

        return [
            'action' => 'confirm-email',
            'flow' => 'user-registration',
            'link' => $this->link,
            'path' => $path,
            'token_masked' => $this->maskToken($token),
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
