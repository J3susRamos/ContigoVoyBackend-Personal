<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contactos extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'celular',
        'email',
        'comentario',
    ];
}
