@include('emails.user-solicitud.partials.layout', [
    'tone' => 'primary',
    'eyebrow' => 'Registro de usuario',
    'title' => 'Confirme su correo electronico',
    'message' => 'Recibimos su solicitud de registro. Para continuar, confirme que este correo pertenece a su cuenta.',
    'buttonLabel' => 'Confirmar correo',
    'buttonUrl' => $link,
    'supportingText' => 'Si usted no realizo esta solicitud, puede ignorar este mensaje.',
])
