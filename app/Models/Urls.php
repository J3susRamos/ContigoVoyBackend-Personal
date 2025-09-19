<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Urls extends Model
{
    use HasFactory;

    protected $table = 'urls';
    protected $primaryKey = 'idUrls';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'enlace',
        'idPadre',
        'iconoName',
    ];

    public function permissions()
    {
        return $this->hasMany(PersonalPermission::class, 'id_urls', 'idUrls');
    }

    public function hijos()
    {
        return $this->hasMany(Urls::class, 'idPadre', 'idUrls');
    }

    public function padre()
    {
        return $this->belongsTo(Urls::class, 'idPadre', 'idUrls');
    }
}
