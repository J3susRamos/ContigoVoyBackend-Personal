Hola {{ $nombre }},

Te recordamos que tienes una cita programada:

Fecha: {{ $fecha }}
Hora: {{ $hora }}

@if (!empty($meet_link))
Enlace de Google Meet: {{ $meet_link }}
@endif

¡No olvides asistir!
