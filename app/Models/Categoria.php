<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $primaryKey = 'idCategoria';
    protected $table = 'categorias';
    protected $fillable = ['nombre'];

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'idCategoria');
    }
}
