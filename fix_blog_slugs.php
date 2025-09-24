<?php
/**
 * Script para verificar y corregir slugs problemáticos
 */

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Models\Blog;

echo "Verificando blogs con problemas de slug...\n\n";

// Buscar blogs sin slug o con slugs problemáticos
$blogs = Blog::whereNull('slug')->orWhere('slug', '')->get();

echo "Blogs sin slug: " . $blogs->count() . "\n";

foreach ($blogs as $blog) {
    echo "ID: {$blog->idBlog} - Tema: {$blog->tema}\n";

    // Generar slug manualmente
    $newSlug = $blog->generateSlug($blog->tema);
    echo "Nuevo slug generado: {$newSlug}\n";

    // Actualizar el blog
    $blog->slug = $newSlug;
    $blog->save();

    echo "Slug actualizado correctamente\n\n";
}

// Buscar blogs con slugs duplicados
$duplicateslugs = Blog::select('slug')
    ->whereNotNull('slug')
    ->where('slug', '!=', '')
    ->groupBy('slug')
    ->havingRaw('COUNT(*) > 1')
    ->pluck('slug');

if ($duplicateSlugsBefore->count() > 0) {
    echo "Slugs duplicados encontrados:\n";
    foreach ($duplicateSlugsBefore as $slug) {
        $blogsWithSameSlug = Blog::where('slug', $slug)->get();
        echo "Slug '{$slug}' usado por:\n";
        foreach ($blogsWithSameSlug as $blog) {
            echo "  - ID: {$blog->idBlog} - Tema: {$blog->tema}\n";
        }
        echo "\n";
    }
}

echo "Verificación completa.\n";
