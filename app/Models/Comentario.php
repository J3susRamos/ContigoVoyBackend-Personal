<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comentario extends Model
{
    use HasFactory;
    protected $table = 'comentarios';
    public $timestamps = false;
    protected $primaryKey = 'idComentario';

    protected $fillable = [
        'nombre',
        'comentario',
        'idBlog',
    ];

    public function blogs(): BelongsTo
    {
        return $this->belongsTo(Blog::class, 'idBlog');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(Respuesta::class, 'idComentario');
    }
}
