<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalPermission extends Model
{
    use HasFactory;

    protected $table = 'personal_permissions';

    protected $fillable = [
        'name_permission',
        'id_user',
    ];

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'id_user', 'user_id');
    }
}
