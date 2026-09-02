@php
    $fechaTurno = optional($solicitud->fecha_turno)?->format('d/m/Y H:i');
@endphp

@include('emails.user-solicitud.partials.layout', [
    'tone' => 'primary',
    'eyebrow' => 'Turnero licencia de conducir',
    'title' => 'Tu turno fue confirmado',
    'message' => "Hola {$solicitud->nombre} {$solicitud->apellido},\n\nYa tenés una fecha y hora asignada para tu trámite de licencia de conducir.",
    'sections' => [
        [
            'title' => 'Fecha y hora asignadas',
            'body' => "Tu turno fue programado para el {$fechaTurno}.",
        ],
        [
            'title' => 'Código de verificación',
            'body' => "Tu código es {$solicitud->codigo_verificacion}. Guardalo porque lo vas a necesitar si querés solicitar un cambio de fecha y hora.",
        ],
        [
            'title' => 'Datos de referencia',
            'body' => "DNI: {$solicitud->dni}\nCorreo electrónico: {$solicitud->email}\nTeléfono: {$solicitud->telefono}",
        ],
    ],
    'supportingText' => 'Si necesitás reprogramar el turno, usá el botón "Gestionar mi turno" e ingresá tu DNI junto con el código de verificación.',
])
