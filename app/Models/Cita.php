<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cita extends Model
{
    use HasFactory;
    protected $table = "citas";
    protected $primaryKey = "idCita";
    public $timestamps = false;

    protected $attributes = [
        "colores" => "#FFA500",
        "estado_Cita" => "Pendiente",
        "idCanal" => "1",
        "idEtiqueta" => "3",
        "idTipoCita" => "2",
        "duracion" => "60",
    ];

    protected $fillable = [
        "idPaciente",
        "idTipoCita",
        "idCanal",
        "idEtiqueta",
        "idPsicologo",
        "idPrePaciente",
        "motivo_Consulta",
        "estado_Cita",
        "colores",
        "duracion",
        "fecha_cita",
        "hora_cita",
        "jitsi_url",
    ];

    public function etiqueta(): BelongsTo
    {
        return $this->belongsTo(Etiqueta::class, foreignKey: "idEtiqueta");
    }

    public function tipoCita(): BelongsTo
    {
        return $this->belongsTo(TipoCita::class, foreignKey: "idTipoCita");
    }

    public function canal(): BelongsTo
    {
        return $this->belongsTo(Canal::class, foreignKey: "idCanal");
    }

    public function prepaciente(): BelongsTo
    {
        return $this->belongsTo(
            PrePaciente::class,
            foreignKey: "idPrePaciente"
        );
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, foreignKey: "idPaciente");
    }

    public function atenciones(): HasMany
    {
        return $this->hasMany(Atencion::class, "idCita");
    }

    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(Psicologo::class, "idPsicologo");
    }

    public function boucher(): HasOne
    {
        return $this->hasOne(Boucher::class, 'idCita');
    }

    public function bouchers(): HasMany
    {
        return $this->hasMany(Boucher::class, 'idCita', 'idCita');
    }
}
