<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recordatorio de Cita</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">

    <table width="100%" cellpadding="0" cellspacing="0"
           style="max-width:600px; margin:auto; background:white; border-radius:12px;
           padding:25px; box-shadow:0 3px 10px rgba(0,0,0,0.12);">

        <tr>
            <td style="text-align:center; padding-bottom:20px;">
                <h2 style="color:#6a1b9a; margin:0;">💜 Recordatorio de tu cita</h2>
                <p style="color:#777; margin-top:5px; font-size:14px;">
                    No olvides tu próxima sesión programada
                </p>
            </td>
        </tr>

        <tr>
            <td>
                <p style="font-size:16px; color:#333;">
                    Hola <strong>{{ $nombre }}</strong>,
                </p>

                <p style="font-size:15px; color:#555;">
                    Te recordamos que tienes una cita programada:
                </p>

                <div style="background:#fafafa; padding:15px; border-radius:10px;
                            border-left:4px solid #8e24aa; margin:18px 0;">
                    <p style="margin:6px 0; font-size:15px;">
                        <strong>📅 Fecha:</strong> {{ $fecha }}
                    </p>
                    <p style="margin:6px 0; font-size:15px;">
                        <strong>⏰ Hora:</strong> {{ $hora }}
                    </p>

                    @if (!empty($jitsi_url))
                        <p style="margin:6px 0; font-size:15px;">
                            <strong>🔗 Enlace:</strong>
                            <a href="{{ $jitsi_url }}" target="_blank" style="color:#6a1b9a">
                                Unirse a la reunión
                            </a>
                        </p>
                    @endif
                </div>

                <p style="font-size:15px; color:#333; margin-top:20px;">
                    ¡No olvides asistir! 😊
                </p>

                @if (!empty($jitsi_url))
                <div style="text-align:center; margin-top:25px;">
                    <a href="{{ $jitsi_url }}"
                       style="background:#6a1b9a; color:white; padding:12px 20px;
                       border-radius:8px; text-decoration:none; display:inline-block;
                       font-weight:bold;">
                        👉 Unirme a la reunión
                    </a>
                </div>
                @endif
            </td>
        </tr>

        <tr>
            <td style="text-align:center; padding-top:25px; color:#999; font-size:12px;">
                ContigoVoy • Plataforma de apoyo emocional
            </td>
        </tr>
    </table>

</body>
</html>
