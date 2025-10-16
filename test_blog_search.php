<?php
/**
 * Script de prueba directa para los blogs problemáticos
 * Ejecuta este script en el servidor para probar las búsquedas sin depender del frontend
 */

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Http\Controllers\Blog\BlogController;
use Illuminate\Http\Request;

echo "🔍 PRUEBA DIRECTA DE BÚSQUEDA DE BLOGS\n";
echo "=====================================\n\n";

// Crear instancia del controller
$controller = new BlogController();

// Lista de blogs problemáticos reportados
$problematicBlogs = [
    'amor-o-dependencia-aprende-a-reconocer-la-diferencia',
    'resiliencia-emocional-como-superar-la-adversidad',
    'crianza-positiva-estrategias-para-educar-con-amor-y-firmeza',
    'como-manejar-la-impulsividad-estrategias-para-tomar-decisiones-mas-conscientes',
    'el-impacto-de-las-redes-sociales-en-la-autoestima-de-los-jovenes',
    'depresion-vs-tristeza-diferencias-clave-que-todos-debemos-conocer',
    'mindfulness-una-herramienta-practica-para-reducir-el-estres-diario'
];

echo "Probando blogs problemáticos:\n\n";

foreach ($problematicBlogs as $index => $slug) {
    echo "--- Prueba " . ($index + 1) . " ---\n";
    echo "Slug: {$slug}\n";

    try {
        // Simular la llamada al método
        $response = $controller->showbyIdBlog($slug);
        $responseData = json_decode($response->getContent(), true);

        if ($responseData['success']) {
            echo "✅ RESULTADO: ÉXITO\n";
            echo "Título encontrado: " . $responseData['result']['tema'] . "\n";
            echo "ID del blog: " . $responseData['result']['id'] . "\n";
        } else {
            echo "❌ RESULTADO: FALLO\n";
            echo "Mensaje: " . $responseData['message'] . "\n";
        }
    } catch (\Exception $e) {
        echo "💥 ERROR: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

// También probar con los títulos originales
echo "Probando con títulos originales:\n\n";

$originalTitles = [
    '¿Amor o dependencia? Aprende a reconocer la diferencia',
    'Resiliencia emocional: cómo superar la adversidad',
    'Crianza positiva: estrategias para educar con amor y firmeza'
];

foreach ($originalTitles as $index => $title) {
    echo "--- Prueba título " . ($index + 1) . " ---\n";
    echo "Título: {$title}\n";

    try {
        $response = $controller->showbyIdBlog($title);
        $responseData = json_decode($response->getContent(), true);

        if ($responseData['success']) {
            echo "✅ RESULTADO: ÉXITO\n";
            echo "Título encontrado: " . $responseData['result']['tema'] . "\n";
        } else {
            echo "❌ RESULTADO: FALLO\n";
            echo "Mensaje: " . $responseData['message'] . "\n";
        }
    } catch (\Exception $e) {
        echo "💥 ERROR: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo "🏁 Pruebas completadas. Revisa los logs para más detalles.\n";
