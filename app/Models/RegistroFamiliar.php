<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroFamiliar extends Model
{
    use HasFactory;
    protected $table = 'registro_familiar';
    protected $primaryKey = 'idRegistro';
    public $timestamps = false;

    protected $fillable = [
        'idPaciente',
        'nombre_madre',
        'estado_madre',
        'nombre_padre',
        'estado_padre',
        'nombre_apoderado',
        'estado_apoderado',
        'cantidad_hijos',
        'cantidad_hermanos',
        'integracion_familiar',
        'historial_familiar'
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'idPaciente');
    }

}
