<?php

namespace App\Mail\TurneroLicenciaConducir;

use App\Contracts\LogsEmailPayload;
use App\Mail\UerSolicitud\Concerns\InteractsWithEmailLogs;
use App\Models\TurneroLicenciaConducir\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TurnoModificadoMail extends Mailable implements LogsEmailPayload
{
    use InteractsWithEmailLogs, Queueable, SerializesModels;

    public function __construct(public Solicitud $solicitud)
    {
        $this->subject('Turno de licencia de conducir modificado');
    }

    public function build()
    {
        return $this->view('emails.turnero-licencia-conducir.turno-modificado', [
            'solicitud' => $this->solicitud,
        ]);
    }

    public function getEmailTemplateKey(): string
    {
        return 'turnero-licencia-conducir.turno-modificado';
    }

    public function getEmailLogPayload(): array
    {
        return [
            'action' => 'turno-modificado',
            'flow' => 'turnero-licencia-conducir',
            'solicitud' => [
                'id' => $this->solicitud->id,
                'dni' => $this->solicitud->dni,
                'estado' => $this->solicitud->estado,
                'fecha_turno' => optional($this->solicitud->fecha_turno)?->toDateTimeString(),
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
