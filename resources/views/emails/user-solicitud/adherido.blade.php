@include('emails.user-solicitud.partials.layout', [
    'tone' => 'success',
    'eyebrow' => 'Registro de usuario',
    'title' => 'Solicitud aprobada',
    'message' => 'Su solicitud de registro fue aprobada correctamente.',
    'supportingText' => 'En caso de corresponder, recibira nuevas instrucciones por los canales informados en el formulario.',
])
