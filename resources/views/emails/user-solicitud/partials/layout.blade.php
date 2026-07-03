@php
    $tone = $tone ?? 'primary';

    $palette = [
        'primary' => ['accent' => '#ff9010', 'soft' => '#fff3e0'],
        'success' => ['accent' => '#1f8f5f', 'soft' => '#eaf8f1'],
        'danger' => ['accent' => '#c54b4b', 'soft' => '#fdeeee'],
    ];

    $colors = $palette[$tone] ?? $palette['primary'];
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: Arial, Helvetica, sans-serif; color: #24303f;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f5f7; margin: 0; padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 640px; background-color: #ffffff; border: 1px solid #e8ebef; border-radius: 16px; overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, {{ $colors['accent'] }} 0%, #fbb32c 100%); padding: 28px 32px;">
                            <div style="font-size: 12px; letter-spacing: 1.4px; text-transform: uppercase; font-weight: 700; color: rgba(255, 255, 255, 0.88);">
                                {{ $eyebrow ?? 'Cutral Co' }}
                            </div>
                            <h1 style="margin: 10px 0 0; font-size: 28px; line-height: 1.2; color: #ffffff;">
                                {{ $title }}
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            <div style="background-color: {{ $colors['soft'] }}; border-left: 4px solid {{ $colors['accent'] }}; border-radius: 12px; padding: 16px 18px; margin-bottom: 24px; font-size: 15px; line-height: 1.7; color: #384657;">
                                {!! nl2br(e($message)) !!}
                            </div>

                            @if (!empty($sections) && is_array($sections))
                                @foreach ($sections as $section)
                                    <div style="margin-bottom: 20px; padding: 18px; border: 1px solid #e8ebef; border-radius: 12px; background-color: #ffffff;">
                                        @if (!empty($section['title']))
                                            <h2 style="margin: 0 0 10px; font-size: 18px; line-height: 1.4; color: #24303f;">
                                                {{ $section['title'] }}
                                            </h2>
                                        @endif

                                        @if (!empty($section['body']))
                                            <p style="margin: 0; font-size: 15px; line-height: 1.7; color: #4d5a69;">
                                                {!! nl2br(e($section['body'])) !!}
                                            </p>
                                        @endif

                                        @if (!empty($section['linkLabel']) && !empty($section['linkUrl']))
                                            <p style="margin: 14px 0 0;">
                                                <a href="{{ $section['linkUrl'] }}" style="color: {{ $colors['accent'] }}; font-weight: 700; text-decoration: none;">
                                                    {{ $section['linkLabel'] }}
                                                </a>
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif

                            @if (!empty($buttonLabel) && !empty($buttonUrl))
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
                                    <tr>
                                        <td>
                                            <a href="{{ $buttonUrl }}" style="display: inline-block; background-color: {{ $colors['accent'] }}; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 22px; border-radius: 12px;">
                                                {{ $buttonLabel }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if (!empty($supportingText))
                                <p style="margin: 0; font-size: 14px; line-height: 1.7; color: #5a6776;">
                                    {!! nl2br(e($supportingText)) !!}
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 32px 28px; font-size: 12px; line-height: 1.6; color: #7a8594;">
                            Municipalidad de Cutral Co
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
