<?php
/**
 * Script para verificar blogs después de aplicar las correcciones
 */

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Models\Blog;

echo "🔍 VERIFICACIÓN DE BLOGS DESPUÉS DE CORRECCIONES\n";
echo "================================================\n\n";

// Lista de blogs problemáticos reportados
$problematicTitles = [
    '¿Amor o dependencia? Aprende a reconocer la diferencia',
    'Resiliencia emocional: cómo superar la adversidad',
    'Crianza positiva: estrategias para educar con amor y firmeza',
    'Cómo manejar la impulsividad: estrategias para tomar decisiones más conscientes',
    'El impacto de las redes sociales en la autoestima de los jóvenes',
    'Depresión vs. tristeza: diferencias clave que todos debemos conocer',
    'Mindfulness: una herramienta práctica para reducir el estrés diario'
];

echo "📝 Verificando blogs reportados como problemáticos:\n";
echo "---------------------------------------------------\n";

$foundCount = 0;
$notFoundCount = 0;

foreach ($problematicTitles as $title) {
    // Buscar por título exacto
    $blog = Blog::where('tema', $title)->first();

    if (!$blog) {
        // Buscar por título similar
        $blog = Blog::where('tema', 'LIKE', '%' . $title . '%')->first();
    }

    if ($blog) {
        $foundCount++;
        echo "✅ ENCONTRADO:\n";
        echo "   ID: {$blog->idBlog}\n";
        echo "   Título: {$blog->tema}\n";
        echo "   Slug: " . ($blog->slug ?: 'NULL') . "\n";
        echo "   Fecha: {$blog->fecha_publicado}\n";

        // Probar la búsqueda por slug
        if ($blog->slug) {
            $testBySlug = Blog::where('slug', $blog->slug)->first();
            echo "   Búsqueda por slug: " . ($testBySlug ? "✅ OK" : "❌ FALLA") . "\n";
        }
        echo "\n";
    } else {
        $notFoundCount++;
        echo "❌ NO ENCONTRADO: {$title}\n\n";
    }
}

echo "📊 RESUMEN:\n";
echo "-----------\n";
echo "Blogs encontrados: {$foundCount}\n";
echo "Blogs no encontrados: {$notFoundCount}\n\n";

// Verificar blogs sin slug
echo "🔍 Verificando blogs sin slug:\n";
echo "------------------------------\n";
$blogsWithoutSlug = Blog::whereNull('slug')->orWhere('slug', '')->get();
echo "Total de blogs sin slug: {$blogsWithoutSlug->count()}\n\n";

if ($blogsWithoutSlug->count() > 0) {
    foreach ($blogsWithoutSlug as $blog) {
        echo "ID: {$blog->idBlog} - Título: {$blog->tema}\n";
    }
    echo "\n";
}

// Verificar slugs duplicados
echo "🔍 Verificando slugs duplicados:\n";
echo "--------------------------------\n";
$duplicateSlugs = Blog::select('slug')
    ->whereNotNull('slug')
    ->where('slug', '!=', '')
    ->groupBy('slug')
    ->havingRaw('COUNT(*) > 1')
    ->pluck('slug');

if ($duplicateSlugs->count() > 0) {
    echo "⚠️  Se encontraron {$duplicateSlugs->count()} slugs duplicados:\n";
    foreach ($duplicateSlugs as $slug) {
        $blogsWithSameSlug = Blog::where('slug', $slug)->get();
        echo "\nSlug '{$slug}' usado por:\n";
        foreach ($blogsWithSameSlug as $blog) {
            echo "  - ID: {$blog->idBlog} - Título: {$blog->tema}\n";
        }
    }
} else {
    echo "✅ No se encontraron slugs duplicados\n";
}

echo "\n🎉 VERIFICACIÓN COMPLETA\n";
