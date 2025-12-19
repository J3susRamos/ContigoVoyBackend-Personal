<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Nueva cita agendada {{ $datos['nombrePaciente'] }}</title>
</head>

<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; padding: 30px; margin:0;">

    <table width="100%" cellpadding="0" cellspacing="0"
        style="max-width: 650px; margin: auto; background: #ffffff; padding: 30px; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.08);">

        <tr>
            <td style="text-align: center; padding-bottom: 25px;">
                <h1 style="font-size: 26px; color: #4B0082; margin: 0;">Hola {{ $datos['psicologo'] }}</h1>
                <p style="color: #555555; font-size: 16px; line-height: 1.5; margin-top: 10px;">
                    Se ha registrado una <strong>nueva cita</strong> mediante la página web de
                    <strong>Contigo Voy</strong>. A continuación, los detalles:
                </p>
            </td>
        </tr>

        <tr>
            <td>
                <div style="padding: 20px; background: #f9f9fc; border-radius: 12px; border-left: 5px solid #6a1b9a; margin-bottom: 25px; font-size: 16px; color: #333;">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: 10px;"><strong>Paciente:</strong> {{ $datos['nombrePaciente'] }}</li>
                        <li style="margin-bottom: 10px;"><strong>Fecha:</strong> {{ $datos['fecha'] }}</li>
                        <li style="margin-bottom: 10px;"><strong>Hora:</strong> {{ $datos['hora'] }}</li>
                        <li style="margin-bottom: 10px;"><strong>Motivo:</strong> {{ $datos['tituloPsicologo'] }} ({{ $datos['motivo'] }})</li>
                    </ul>
                </div>
            </td>
        </tr>

        <tr>
            <td style="padding-bottom: 25px;">
                <p style="font-size: 16px; color: #4B0082; font-weight: 600; margin-bottom: 8px;">👉 Acción requerida:</p>
                <p style="font-size: 15px; color: #555555; margin: 0;">Revisar agenda y confirmar disponibilidad.</p>

                <p style="font-size: 15px; color: #555555; margin: 0;">Saludos,</p>

                <p style="font-size: 15px; color: #555555; margin: 0;"> <strong>Equipo Contigo Voy</strong></p>
            </td>
        </tr>

        @if (!empty($jitsi_url))
        <tr>
            <td style="text-align: center; padding-bottom: 25px;">
                <p style="margin-bottom: 10px; font-size: 16px; color: #4B0082; font-weight: 600;">Enlace de la reunión:</p>
                <a href="{{ $jitsi_url }}"
                    style="display: inline-block; background: #6a1b9a; color: white; padding: 14px 25px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 16px;">
                    Unirse a la reunión
                </a>
            </td>
        </tr>
        @endif

        <tr>
            <td style="text-align: center; padding-top: 20px; color: #aaaaaa; font-size: 12px;">
                ContigoVoy • Plataforma de apoyo emocional 💜
            </td>
        </tr>

    </table>

</body>

</html>
