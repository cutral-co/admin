@include('emails.user-solicitud.partials.layout', [
    'tone' => 'success',
    'eyebrow' => 'Registro de usuario',
    'title' => 'Correo confirmado',
    'message' => 'Su correo electronico fue confirmado correctamente.',
    'supportingText' => 'La solicitud quedo pendiente de validacion administrativa.',
])
