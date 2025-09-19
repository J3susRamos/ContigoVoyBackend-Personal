<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h2>Nuevo mensaje de contacto</h2>
    <p><strong>Nombre:</strong> {{ $datos['nombre'] }}</p>
    <p><strong>Apellido:</strong> {{ $datos['apellido'] }}</p>
    <p><strong>Celular:</strong> {{ $datos['celular'] }}</p>
    <p><strong>Email:</strong> {{ $datos['email'] }}</p>
    <p><strong>Comentario:</strong> {{ $datos['comentario'] }}</p>
</body>
</html>