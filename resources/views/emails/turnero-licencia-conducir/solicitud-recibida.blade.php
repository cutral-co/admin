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
            'title' => 'Datos registrados',
            'body' => "DNI: {$solicitud->dni}\nCorreo electrónico: {$solicitud->email}\nTeléfono: {$solicitud->telefono}",
        ],
    ],
    'supportingText' => 'Recordá presentarte con la documentación necesaria para realizar el trámite.',
])
