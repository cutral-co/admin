<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 42px 48px; }
        body { color: #24303f; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.55; }
        .header { border-bottom: 3px solid #ff9010; margin-bottom: 28px; padding-bottom: 16px; }
        .eyebrow { color: #d87900; font-size: 10px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        h1 { font-size: 23px; margin: 7px 0 0; }
        h2 { color: #d87900; font-size: 13px; margin: 22px 0 8px; }
        .meta { background: #fff3e0; border-left: 4px solid #ff9010; padding: 12px 14px; }
        .label { color: #5a6776; font-weight: bold; }
        .content { border: 1px solid #e8ebef; min-height: 85px; padding: 14px; white-space: pre-wrap; }
        .footer { bottom: 0; color: #7a8594; font-size: 9px; position: fixed; }
    </style>
</head>
<body>
    <div class="header">
        <div class="eyebrow">Municipalidad de Cutral Co</div>
        <h1>Constancia de notificación electrónica</h1>
    </div>

    <div class="meta">
        <div><span class="label">Notificación N.º:</span> {{ $notificacion->id }}</div>
        <div><span class="label">Fecha y hora:</span> {{ $fechaNotificacion->format('d/m/Y H:i') }}</div>
        <div><span class="label">Origen:</span> {{ $notificacion->origin?->descripcion ?? 'Sin origen' }}</div>
        <div><span class="label">Destinatario:</span> {{ $domicilio->nombre }}</div>
        <div><span class="label">CUIT del destinatario:</span> {{ $domicilio->documento }}</div>
        <div><span class="label">Hash de verificación:</span> {{ $notificacion->hash }}</div>
    </div>

    <h2>Asunto</h2>
    <div class="content">{{ $notificacion->title }}</div>

    <h2>Contenido de la notificación</h2>
    <div class="content">{{ $notificacion->body }}</div>

    <h2>Archivos adjuntos</h2>
    @if ($notificacion->archivos->isEmpty())
        <div class="content">No se adjuntaron archivos.</div>
    @else
        <div class="content">
            <ul>
                @foreach ($notificacion->archivos as $archivo)
                    <li>{{ $archivo->name }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="footer">Documento generado por el Sistema de Domicilio Electrónico.</div>
</body>
</html>
