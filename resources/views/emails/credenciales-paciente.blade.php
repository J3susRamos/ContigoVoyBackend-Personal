<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Credenciales de Acceso</title>
</head>

<body>
    <h2>Hola {{ $nombre }},</h2>

    <p>Se ha creado tu cuenta de paciente. Aquí tienes tus credenciales de acceso:</p>

    <p><strong>Correo electrónico:</strong> {{ $email }}</p>
    <p><strong>Contraseña temporal:</strong> {{ $password }}</p>

    <p>Por favor, inicia sesión.</p>

    <p>
        <a href="{{ $loginURL }}" style="display:inline-block;padding:10px 20px;background-color:#4CAF50;color:white;text-decoration:none;border-radius:5px;">
            Iniciar sesión
        </a>
    </p>

    <p>Gracias por confiar en nosotros.</p>
</body>

</html>