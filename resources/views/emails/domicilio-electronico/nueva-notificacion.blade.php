@include('emails.user-solicitud.partials.layout', [
    'tone' => 'primary',
    'eyebrow' => 'Domicilio electrónico',
    'title' => 'Tiene una nueva notificación',
    'message' => 'Le informamos, a modo de cortesía, que recibió una nueva notificación en su domicilio electrónico.',
    'sections' => [
        [
            'title' => 'Consulte su casilla',
            'body' => 'Ingrese a Cutral Digital para revisar la notificación y sus archivos adjuntos, si los hubiera.',
        ],
    ],
    'buttonLabel' => 'Ingresar a Cutral Digital',
    'buttonUrl' => $loginUrl,
    'supportingText' => 'Este correo es únicamente un aviso. La notificación se encuentra disponible en su domicilio electrónico.',
])
