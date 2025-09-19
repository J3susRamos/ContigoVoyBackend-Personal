<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Canal extends Model
{
    use HasFactory;
    protected $table = 'canales';
    protected $primaryKey = 'idCanal';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
