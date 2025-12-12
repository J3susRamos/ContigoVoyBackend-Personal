<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Psicologo extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'psicologos';
    protected $primaryKey = 'idPsicologo';
    protected $fillable = [
        'titulo',
        'introduccion',
        'user_id',
        'pais',
        'genero',
        'experiencia',
        'horario',
    ];

    protected $casts = [
        'horario' => 'array',
    ];

    // Relación muchos a muchos con Especialidades
    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(Especialidad::class, 'especialidad_detalle', 'idPsicologo', 'idEspecialidad');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'idPsicologo', 'idPsicologo');
    }

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class, 'idPaciente', 'idPaciente');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function idiomas(): BelongsToMany
    {
        return $this->belongsToMany(Idioma::class, 'idioma_detalle', 'idPsicologo', 'idIdioma');
    }

}
