<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enfermedad extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'enfermedades';
    protected $primaryKey = 'idEnfermedad';

    public function citas(): HasMany
    {
        return $this->hasMany(Atencion::class, 'idEnfermedad');
    }
}
