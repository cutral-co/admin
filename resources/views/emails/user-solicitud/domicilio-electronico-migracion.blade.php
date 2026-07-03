@include('emails.user-solicitud.partials.layout', [
    'tone' => 'primary',
    'eyebrow' => 'Domicilio electrónico',
    'title' => 'Nuevo acceso disponible',
    'message' => 'Actualizamos la implementación de la casilla de domicilio electrónico para que pueda recibir y consultar notificaciones digitales desde el sistema municipal.',
    'sections' => [
        [
            'title' => 'Implementación de la casilla',
            'body' => 'A partir de esta implementación, sus comunicaciones oficiales se centralizan en una casilla de domicilio electrónico dentro de la plataforma. Le recomendamos ingresar y revisar periódicamente las novedades.',
        ],
        [
            'title' => 'Términos y condiciones',
            'body' => 'Puede consultar los términos y condiciones vigentes en el siguiente enlace.',
            'linkLabel' => 'Ver términos y condiciones',
            'linkUrl' => $termsUrl,
        ],
        [
            'title' => 'Credenciales de acceso',
            'body' => "CUIT: {$cuit}\nContraseña: {$password}",
        ],
    ],
    'supportingText' => 'Si ya tenía una clave anterior, fue reemplazada por la informada en este correo.',
])
