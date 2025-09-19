<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Atencion extends Model
{
    protected $table = 'atenciones';
    protected $primaryKey = 'idAtencion';
    public $timestamps = false;

    protected $fillable = [
        'idCita',
        'motivoConsulta',
        'formaContacto',
        'diagnostico',
        'tratamiento',
        'observacion',
        'ultimosObjetivos',
        'idEnfermedad',
        'comentario',
        'documentosAdicionales',
        'fechaAtencion',
        'descripcion',
    ];

    public function enfermedad(): BelongsTo
    {
        return $this->belongsTo(Enfermedad::class, 'idEnfermedad');
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'idCita');
    }
}
