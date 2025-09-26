<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        "idCita",
        "tipo_notificacion",
        "telefono",
        "mensaje",
        "estado",
        "whatsapp_message_id",
        "error_mensaje",
        "fecha_programada",
        "fecha_enviado",
    ];

    protected $casts = [
        "fecha_programada" => "datetime",
        "fecha_enviado" => "datetime",
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, "idCita", "idCita");
    }
}
