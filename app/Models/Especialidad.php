<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Especialidad extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $primaryKey = 'idEspecialidad';
    protected $table = 'especialidades';
    protected $fillable = ['nombre'];


    public function psicologos(): BelongsToMany
    {
        return $this->belongsToMany(Psicologo::class, 'especialidad_detalle', 'idEspecialidad', 'idPsicologo');
    }

}
