<?php
// Scrip para crear 20 notificaciones via API
$loginUrl = 'http://api.cutralco/api/login';
$notifyUrl = 'http://api.cutralco/api/user/domicilio-electronico/enviar-notificacion';

// Login
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['cuit' => '77777777777', 'password' => 'password']));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$token = $data['data']['token'] ?? null;

if (!$token) {
    echo "Error login: " . $response . "\n";
    exit(1);
}
echo "Login OK. Token: " . substr($token, 0, 30) . "...\n";

// Crear 20 notificaciones
$notificaciones = [
    ['title' => 'Nuevo Tributo Municipal 2026', 'body' => 'Se ha registrado un nuevo tributo municipal para el ejercicio 2026.', 'origin' => 'mi-cutral-digital'],
    ['title' => 'Recordatorio de Vencimiento', 'body' => 'Le informamos que su tasa vial vence el proximo 30 de abril.', 'origin' => 'test'],
    ['title' => 'Cambio de Horario de Atencion', 'body' => 'A partir del lunes proximo, el horario de atencion sera de 8:00 a 13:00hs.', 'origin' => 'test'],
    ['title' => 'Mantenimiento Programado', 'body' => 'El sistema estara en mantenimiento el domingo.', 'origin' => 'mi-cutral-digital'],
    ['title' => 'Nueva Funcionalidad Disponible', 'body' => 'Ahora puede gestionar sus tramites desde la app.', 'origin' => 'mi-cutral-digital'],
    ['title' => 'Verificacion de Cuenta', 'body' => 'Su cuenta ha sido verificada exitosamente.', 'origin' => 'mi-cutral-digital'],
    ['title' => 'Actualizacion de Datos', 'body' => 'Sus datos han sido actualizados.', 'origin' => 'test'],
    ['title' => 'Certificado de Residentado', 'body' => 'Su certificado ha sido emitido.', 'origin' => 'test'],
    ['title' => 'Inspeccion Programada', 'body' => 'Se ha programado una inspeccion.', 'origin' => 'test'],
    ['title' => 'Encuesta de Satisfaccion', 'body' => 'Participe de nuestra encuesta.', 'origin' => 'mi-cutral-digital'],
    ['title' => 'Alerta de Seguridad', 'body' => 'Se detecto un acceso desde nuevo dispositivo.', 'origin' => 'mi-cutral-digital'],
    ['title' => 'Convenio de Pago', 'body' => 'Se aprobo un convenio de pago.', 'origin' => 'test'],
    ['title' => 'Tramite Completado', 'body' => 'Su tramite ha sido completado.', 'origin' => 'test'],
    ['title' => 'Documento Disponible', 'body' => 'Ya puede descargar el documento.', 'origin' => 'mi-cutral-digital'],
    ['title' => 'Calendario de Pago 2026', 'body' => 'Consulte el calendario de pagos.', 'origin' => 'test'],
    ['title' => 'Cambio de Categoria', 'body' => 'Su categoria ha sido actualizada.', 'origin' => 'test'],
    ['title' => 'Notificacion Importante', 'body' => 'Tiene una notificacion pendiente.', 'origin' => 'mi-cutral-digital'],
    ['title' => 'Resumen Mensual', 'body' => 'Su resumen mensual disponible.', 'origin' => 'test'],
    ['title' => 'Vencimiento Proximo', 'body' => 'Su impuesto vence en 5 dias.', 'origin' => 'test'],
    ['title' => 'Bienvenido al Sistema', 'body' => 'Gracias por registrarse en DE.', 'origin' => 'mi-cutral-digital'],
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $notifyUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

foreach ($notificaciones as $i => $notif) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notif));
    $result = curl_exec($ch);
    $resultData = json_decode($result, true);
    $status = $resultData['error'] ? "ERROR: " . $resultData['error'] : "OK";
    echo "Notificacion " . ($i+1) . ": " . $notif['title'] . " - " . $status . "\n";
}

curl_close($ch);
echo "\n=== 20 notificaciones creadas ===\n";