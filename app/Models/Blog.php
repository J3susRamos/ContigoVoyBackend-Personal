<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blog extends Model
{
    use HasFactory;

    protected $table = 'blogs';
    protected $primaryKey = 'idBlog';
    public $timestamps = false;

    protected $fillable = [
        'idCategoria',
        'tema',
        'contenido',
        'imagenes',
        'idPsicologo',
    ];

    protected $casts = [
        'imagenes' => 'array',
    ];

    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(Psicologo::class, 'idPsicologo', 'idPsicologo');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'idCategoria');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class, 'idBlog');
    }
}
