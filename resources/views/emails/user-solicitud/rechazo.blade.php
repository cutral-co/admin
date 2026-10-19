@include('emails.user-solicitud.partials.layout', [
    'tone' => 'danger',
    'eyebrow' => 'Registro de usuario',
    'title' => 'Solicitud rechazada',
    'message' => 'Su solicitud de registro no pudo ser aprobada.',
    'sections' => [[
        'title' => 'Motivo del rechazo',
        'body' => $motivo,
    ]],
    'supportingText' => 'Si necesita mas informacion, comuniquese por los canales oficiales del municipio.',
])
