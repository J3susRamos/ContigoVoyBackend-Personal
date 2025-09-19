<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Respuesta extends Model
{
    use HasFactory;
    protected $primaryKey = 'idRespuesta';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'respuesta',
        'idComentario',
    ];

    public function comentarios(): BelongsTo
    {
        return $this->belongsTo(Comentario::class, 'idComentario');
    }
}
