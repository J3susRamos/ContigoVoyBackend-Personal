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

    protected $table = 'blogs';
    protected $primaryKey = 'idBlog';
    public $timestamps = false;

    protected $fillable = [
        'idCategoria',
        'tema',
        'slug',
        'contenido',
        'imagenes',
        'idPsicologo',
        'fecha_publicado',
    ];

    protected $casts = [
        'imagenes' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = $blog->generateSlug($blog->tema);
            }
        });

        static::updating(function ($blog) {
            // Solo regenerar slug si el título cambió Y no hay un slug personalizado
            if ($blog->isDirty('tema')) {
                $currentSlug = $blog->getOriginal('slug');
                $expectedSlug = $blog->createSlugFromTitle($blog->getOriginal('tema'));

                // Solo regenerar slug si el slug actual coincide con el slug auto-generado anterior
                // Esto permite slugs personalizados
                if ($currentSlug === $expectedSlug || empty($currentSlug)) {
                    $blog->slug = $blog->generateSlug($blog->tema);
                }
            }
        });
    }

    /**
     * Generar slug único basado en el tema
     */
    public function generateSlug(string $title): string
    {
        $baseSlug = $this->createSlugFromTitle($title);
        return $this->ensureUniqueSlug($baseSlug);
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
     * Asegurar que el slug sea único
     */
    private function ensureUniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function psicologo(): BelongsTo
    {
        return $this->belongsTo(Psicologo::class, 'idPsicologo', 'idPsicologo');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class, 'idBlog', 'id');
    }
}
