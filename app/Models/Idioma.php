<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Psicologo;

class Idioma extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'idiomas';
    protected $primaryKey = 'idIdioma';
    protected $fillable = ['nombre'];

    public function psicologos(): BelongsToMany {
        return $this->belongsToMany(Psicologo::class, 'idioma_detalle', 'idIdioma', 'idPsicologo');
    }
}