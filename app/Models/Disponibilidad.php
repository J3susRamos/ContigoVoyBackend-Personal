<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Disponibilidad extends Model
{
    use HasFactory;

    protected $primaryKey = 'idDisponibilidad';
    public $timestamps = true;
    protected $table = 'disponibilidad';

    protected $fillable = [
        'idPsicologo',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'turno',
    ];

    public function psicologo()
    {
        return $this->belongsTo(Psicologo::class, 'idPsicologo', 'idPsicologo');
    }
}
