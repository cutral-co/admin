@include('emails.user-solicitud.partials.layout', [
    'tone' => 'primary',
    'eyebrow' => 'Turnero licencia de conducir',
    'title' => 'Turno confirmado',
    'message' => "Hola {$solicitud->nombre} {$solicitud->apellido},\n\nTu turno para trámites de licencia de conducir quedó confirmado.",
    'sections' => [
        [
            'title' => 'Fecha y hora',
            'body' => $solicitud->fecha_turno->format('d/m/Y H:i') . ' hs.',
        ],
        [
            'title' => 'Estado',
            'body' => 'El turno fue aprobado automáticamente.',
        ],
        [
            'title' => 'Código de autogestión',
            'html' => 'Tu código es <strong style="font-size: 22px; letter-spacing: 3px; color: #c56f00;">' . e($solicitud->codigo_verificacion) . '</strong>. Guardalo para consultar o cambiar tu turno.',
        ],
        [
            'title' => 'Datos registrados',
            'body' => "DNI: {$solicitud->dni}\nCorreo electrónico: {$solicitud->email}\nTeléfono: {$solicitud->telefono}",
        ],
    ],
    'supportingText' => 'Recordá presentarte con la documentación necesaria para realizar el trámite.',
])
