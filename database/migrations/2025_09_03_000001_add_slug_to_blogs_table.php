<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Blog;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('tema');
            $table->index('slug');
        });

        // Hacer el slug requerido después de generar los slugs existentes
        $this->generateSlugsForExistingBlogs();

        // Ahora hacer el campo requerido
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }

    /**
     * Generar slugs para blogs existentes
     */
    private function generateSlugsForExistingBlogs(): void
    {
        $blogs = Blog::whereNull('slug')->get();
        $usedSlugs = [];

        foreach ($blogs as $blog) {
            $baseSlug = $this->createSlug($blog->tema);
            $slug = $this->ensureUniqueSlug($baseSlug, $usedSlugs);

            $blog->update(['slug' => $slug]);
            $usedSlugs[] = $slug;
        }
    }

    /**
     * Crear slug desde el título
     */
    private function createSlug(string $title): string
    {
        // Convertir a minúsculas y eliminar caracteres especiales
        $slug = Str::lower($title);

        // Reemplazar caracteres especiales del español
        $slug = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
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
    private function ensureUniqueSlug(string $baseSlug, array $usedSlugs): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (in_array($slug, $usedSlugs) || Blog::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
};
