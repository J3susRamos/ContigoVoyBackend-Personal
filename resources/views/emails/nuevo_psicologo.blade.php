<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body>
    <h2>Hola {{ $nombreCompleto }},</h2>

    <p>Has sido registrado como psicólogo en nuestra plataforma.</p>

    @if ($especialidades)
        <p><strong>Especialidades:</strong>
            @if (is_array($especialidades) || $especialidades instanceof \Illuminate\Support\Collection)
                {{ implode(', ', (array) $especialidades) }}
            @else
                {{ $especialidades }}
            @endif
        </p>

        <p>
            <strong>Tu usuario es:</strong> {{ $usuario }}<br>
            <strong>Tu contraseña es:</strong> {{ $password }}
        </p>

        <p>
            Te recomendamos cambiar tu contraseña después de iniciar sesión por primera vez.
        </p>

        <p>
        <div class="cta">
            <a href="https://centropsicologicocontigovoy.com/">Visitar nuestra plataforma</a>
        </div>

        </p>
    @endif


    <hr>
    <p>Atentamente,<br>Equipo de la plataforma</p>
</body>

</html>
