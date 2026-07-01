@include('emails.user-solicitud.partials.layout', [
    'tone' => 'success',
    'eyebrow' => 'Registro de usuario',
    'title' => 'Solicitud aprobada',
    'message' => "Su solicitud de registro fue aprobada correctamente.\n\nYa puede ingresar al sistema con las siguientes credenciales:\nCUIT: {$cuit}\nContraseña: {$password}",
    /* 'supportingText' => 'Le recomendamos cambiar su contraseña despues del primer ingreso.', */
])
