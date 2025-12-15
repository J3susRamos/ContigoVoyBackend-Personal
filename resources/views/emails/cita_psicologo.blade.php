<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cita con el paciente</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px;">

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">

        <tr>
            <td style="text-align: center; padding-bottom: 20px;">
                <h2 style="color: #6a1b9a; margin: 0;">
                    🌿 Nueva cita programada
                </h2>
                <p style="color: #555; margin-top: 5px;">
                    Se ha asignado una consulta con un nuevo paciente.
                </p>
            </td>
        </tr>

        <tr>
            <td>

                <div style="padding: 15px; background: #fafafa; border-radius: 10px; border-left: 4px solid #6a1b9a; margin-bottom: 20px;">
                    <p style="margin: 6px 0;"><strong>Paciente:</strong> {{ $datos['nombrePaciente'] }}</p>
                    <p style="margin: 6px 0;"><strong>Correo:</strong> {{ $datos['correoPaciente'] }}</p>
                    <p style="margin: 6px 0;"><strong>Celular:</strong> {{ $datos['celularPaciente'] }}</p>
                </div>

                <div style="padding: 15px; background: #fafafa; border-radius: 10px; border-left: 4px solid #8e24aa; margin-bottom: 20px;">
                    <p style="margin: 6px 0;"><strong>Fecha:</strong> {{ $datos['fecha'] }}</p>
                    <p style="margin: 6px 0;"><strong>Hora:</strong> {{ $datos['hora'] }}</p>
                    <p style="margin: 6px 0;"><strong>Psicólogo:</strong> {{ $datos['psicologo'] }}</p>
                </div>

                @if(!empty($jitsi_url))
                    <div style="text-align: center; margin-top: 20px;">
                        <p style="margin-bottom: 10px; font-size: 16px;"><strong>Enlace de la reunión:</strong></p>

                        <a href="{{ $jitsi_url }}"
                           style="display: inline-block; background: #6a1b9a; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: bold;">
                           Unirse a la reunión
                        </a>

                        <p style="color: #888; font-size: 14px; margin-top: 10px;">
                            Si el botón no funciona, copia este enlace: <br>
                            <span style="font-size: 13px;">{{ $jitsi_url }}</span>
                        </p>
                    </div>
                @endif

            </td>
        </tr>

        <tr>
            <td style="text-align: center; padding-top: 25px; color: #888; font-size: 12px;">
                ContigoVoy • Plataforma de apoyo emocional 💜
            </td>
        </tr>

    </table>

</body>
</html>
