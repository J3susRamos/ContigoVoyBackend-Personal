<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCita extends Model
{
    use HasFactory;
    protected $table = 'tipo_citas';
    protected $primaryKey = 'idTipoCita';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'idTipoCita');
    }
}
