@php
    $fechaTurno = optional($solicitud->fecha_turno)?->format('d/m/Y H:i');
@endphp

@include('emails.user-solicitud.partials.layout', [
    'tone' => 'primary',
    'eyebrow' => 'Turnero licencia de conducir',
    'title' => 'Tu turno fue modificado',
    'message' => "Hola {$solicitud->nombre} {$solicitud->apellido},\n\nLa fecha y hora de tu turno de licencia de conducir fueron modificadas.",
    'sections' => [
        [
            'title' => 'Nueva fecha y hora',
            'body' => "Tu turno quedó programado para el {$fechaTurno}.",
        ],
        [
            'title' => 'Código de autogestión',
            'html' => 'Tu código es <strong style="font-size: 22px; letter-spacing: 3px; color: #c56f00;">' . e($solicitud->codigo_verificacion) . '</strong>. Guardalo para consultar o cambiar tu turno.',
        ],
        [
            'title' => 'Datos de referencia',
            'body' => "DNI: {$solicitud->dni}\nCorreo electrónico: {$solicitud->email}\nTeléfono: {$solicitud->telefono}",
        ],
    ],
    'supportingText' => 'Si necesitás volver a gestionar el turno, ingresá tu DNI y el código de autogestión.',
])
