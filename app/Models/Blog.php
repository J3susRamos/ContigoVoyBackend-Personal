<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $table = 'posts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'tema',
        'especialidad', // categoría
        'descripcion', // contenido
        'imagen',
        'psicologo_id',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            // Agregar fecha si no existe
            if (empty($blog->fecha)) {
                $blog->fecha = now();
            }

            // Log para debugging
            \Log::info("Creando blog con título: " . $blog->tema);
        });

        static::updating(function ($blog) {
            \Log::info("Actualizando blog ID: " . $blog->id);
        });
    }

    /**
     * Generar slug dinámicamente desde el título
     */
    public function getSlugAttribute(): string
    {
        return $this->createSlugFromTitle($this->tema);
    }

    /**
     * Generar slug único basado en el tema
     */
    public function generateSlug(string $title): string
    {
        $baseSlug = $this->createSlugFromTitle($title);
        return $baseSlug; // No verificamos unicidad ya que no se almacena
    }

    /**
     * Crear slug desde el título
     */
    private function createSlugFromTitle(string $title): string
    {
        // Convertir a minúsculas
        $slug = Str::lower($title);

        // Reemplazar caracteres especiales del español
        $slug = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü', 'ç'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u', 'c'],
            $slug
        );

        // Eliminar caracteres que no sean letras, números, espacios o guiones
        $slug = preg_replace('/[^\w\s-]/', '', $slug);

        // Reemplazar espacios múltiples con uno solo
        $slug = preg_replace('/\s+/', ' ', $slug);

        // Reemplazar espacios con guiones
        $slug = str_replace(' ', '-', $slug);

        // Eliminar guiones múltiples
        $slug = preg_replace('/-+/', '-', $slug);

        // Eliminar guiones al inicio y final
        $slug = trim($slug, '-');

        // Limitar longitud
        return Str::limit($slug, 100, '');
    }

    /**
     * Buscar blog por slug generado dinámicamente
     */
    public static function findBySlug(string $slug)
    {
        // Convertir slug de vuelta a formato de búsqueda
        $searchTerm = str_replace('-', ' ', $slug);
        return static::where('tema', 'LIKE', '%' . $searchTerm . '%')->first();
    }

    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(Psicologo::class, 'psicologo_id', 'idPsicologo');
    }

    // Relación con categoría basada en el campo especialidad (como string)
    public function categoria()
    {
        // Esta será una relación simulada ya que especialidad es un string
        return (object) ['nombre' => $this->especialidad];
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class, 'idBlog', 'id');
    }
}
