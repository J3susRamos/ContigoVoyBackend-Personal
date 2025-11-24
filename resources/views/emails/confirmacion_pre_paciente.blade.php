<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f6fb;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #9494F3;
            padding: 30px 20px;
            text-align: center;
            color: white;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 30px 20px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.6;
        }

        .highlight {
            color: #9494F3;
            font-weight: bold;
            text-transform: capitalize;
        }

        .info-box {
            background-color: #eef0ff;
            border-left: 4px solid #9494F3;
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-box li {
            margin: 10px 0;
            font-size: 15px;
        }

        .cta {
            display: block;
            text-align: center;
            margin: 30px 0;
        }

        .cta a {
            background-color: #9494F3;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
        }

        .footer {
            font-size: 13px;
            color: #777;
            text-align: center;
            padding: 20px;
        }

        @media screen and (max-width: 600px) {
            .container {
                margin: 20px;
            }

            .header h1 {
                font-size: 20px;
            }

            .content p,
            .info-box li {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>¡Confirmación de tu primera cita!</h1>
        </div>
        <div class="content">
            <p>Hola <span class="highlight">{{ $datos['nombre'] ?? 'Paciente' }}</span>,</p>
            <p>Nos alegra acompañarte en este proceso de crecimiento personal. 🌱</p>
            <p>Gracias por confiar en <strong>Centro Psicológico Contigo Voy</strong>.</p>
            <p>A continuación encontrarás los detalles:</p>

            <div class="info-box">
                <ul>
                    <li><strong>📅 Fecha:</strong> {{ $datos['fecha'] ?? 'No disponible' }}</li>
                    <li><strong>🕒 Hora:</strong> {{ $datos['hora'] ?? 'No disponible' }}</li>
                    <li><strong>🧠 Psicólogo:</strong> {{ $datos['psicologo'] ?? 'No disponible' }}</li>
                </ul>
            </div>

            <div class="cta">
                <a href="https://centropsicologicocontigovoy.com/">Visitar nuestra plataforma</a>
            </div>

            <p>Nos comunicaremos contigo pronto para confirmar tu cita.</p>
        </div>
        <div class="footer">
            © {{ date('Y') }} Contigo Voy · Todos los derechos reservados
        </div>
    </div>
</body>

</html>