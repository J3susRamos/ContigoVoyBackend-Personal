<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrePaciente extends Model
{
    use HasFactory;

    protected $table = 'pre_pacientes';
    protected $primaryKey = 'idPrePaciente';
    public $timestamps = true;

    protected $attributes = [
        'estado' => 'pendiente',
    ];

    protected $fillable = [
        'nombre',
        'celular',
        'correo',
        'idPsicologo',
    ];

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'idPrePaciente');
    }

    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(Psicologo::class, 'idPsicologo', 'idPsicologo');
    }
}
