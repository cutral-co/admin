<?php

namespace App\Mail\TurneroLicenciaConducir;

use App\Contracts\LogsEmailPayload;
use App\Mail\UerSolicitud\Concerns\InteractsWithEmailLogs;
use App\Models\TurneroLicenciaConducir\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TurnoCanceladoMail extends Mailable implements LogsEmailPayload
{
    use InteractsWithEmailLogs, Queueable, SerializesModels;

    public function __construct(
        public Solicitud $solicitud,
        public string $motivo,
    ) {
        $this->subject('Turno de licencia de conducir cancelado');
    }

    public function build()
    {
        return $this->view('emails.turnero-licencia-conducir.turno-cancelado', [
            'solicitud' => $this->solicitud,
            'motivo' => $this->motivo,
        ]);
    }

    public function getEmailTemplateKey(): string
    {
        return 'turnero-licencia-conducir.turno-cancelado';
    }

    public function getEmailLogPayload(): array
    {
        return [
            'action' => 'turno-cancelado',
            'flow' => 'turnero-licencia-conducir',
            'solicitud' => [
                'id' => $this->solicitud->id,
                'dni' => $this->solicitud->dni,
                'estado' => $this->solicitud->estado,
                'motivo_cancelacion' => $this->motivo,
            ],
        ];
    }

    public function getEmailLogMeta(): array
    {
        return [
            'module' => 'turnero-licencia-conducir',
            'template_group' => 'turnero-licencia-conducir',
        ];
    }
}
