<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Etiqueta extends Model
{
    use HasFactory;
    protected $table = 'etiquetas';
    protected $primaryKey = 'idEtiqueta';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'idEtiqueta');
    }
}
