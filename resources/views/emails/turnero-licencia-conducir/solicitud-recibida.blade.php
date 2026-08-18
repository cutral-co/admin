@include('emails.user-solicitud.partials.layout', [
    'tone' => 'primary',
    'eyebrow' => 'Turnero licencia de conducir',
    'title' => 'Solicitud de turno recibida',
    'message' => "Hola {$solicitud->nombre} {$solicitud->apellido},\n\nRecibimos correctamente tu solicitud de turno para trámites de licencia de conducir.",
    'sections' => [
        [
            'title' => 'Estado de la solicitud',
            'body' => 'Tu pedido quedó registrado en el sistema con estado pendiente.',
        ],
        [
            'title' => 'Próximo paso',
            'body' => 'Un agente municipal se pondrá en contacto para continuar con la gestión y coordinar los pasos siguientes.',
        ],
        [
            'title' => 'Datos registrados',
            'body' => "CUIT: {$solicitud->cuit}\nCorreo electrónico: {$solicitud->email}\nTeléfono: {$solicitud->telefono}",
        ],
    ],
    'supportingText' => 'Si necesitás volver a cargar una solicitud, podés hacerlo desde el portal público del turnero.',
])
