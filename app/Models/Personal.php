<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personal extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'apellido',
        'email',
        'password',
        'fecha_nacimiento',
        'fecha_creacion',
        'imagen',
        'rol',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_creacion'   => 'datetime',
    ];

    public $timestamps = false;

    public function permissions(): HasMany
    {
        return $this->hasMany(PersonalPermission::class, 'id_user', 'user_id');
    }

    public function getEdadAttribute(): int
    {
        return Carbon::parse($this->fecha_nacimiento)->age;
    }
}

