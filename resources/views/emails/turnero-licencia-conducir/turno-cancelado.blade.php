@include('emails.user-solicitud.partials.layout', [
    'tone' => 'danger',
    'eyebrow' => 'Turnero licencia de conducir',
    'title' => 'Tu turno fue cancelado',
    'message' => "Hola {$solicitud->nombre} {$solicitud->apellido},\n\nTu turno de licencia de conducir fue cancelado.",
    'sections' => [[
        'title' => 'Motivo de la cancelación',
        'body' => $motivo,
    ]],
    'supportingText' => 'Podés solicitar un nuevo turno ingresando al turnero de licencia de conducir.',
])
