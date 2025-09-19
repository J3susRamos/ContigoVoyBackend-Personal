<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marketing extends Model
{
    use HasFactory;

    protected $table = 'plantilla_marketing'; // Nombre de la tabla real
    protected $primaryKey = 'id_plantilla';
    public $timestamps = false; // Si no tienes created_at / updated_at

    protected $fillable = [
        'idPsicologo',
        'nombre',
        'asunto',
        'remitente',
        'destinatarios',
        'bloques',
        'fecha_creacion',
        'estado',
    ];

    protected $casts = [
        'bloques' => 'array',                 
        'fecha_creacion' => 'datetime',      
        'estado' => 'boolean',               
    ];

    // Relación con Psicologo
    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(Psicologo::class, 'idPsicologo', 'idPsicologo');
    }


    protected static function booted()
    {
        static::creating(function ($marketing) {
            if (empty($marketing->fecha_creacion)) {
                $marketing->fecha_creacion = now();
            }
        });
    }
}
