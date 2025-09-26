#!/bin/bash

# Script para aplicar los cambios en producción
echo "🚀 Aplicando correcciones de blogs en producción..."

# Navegar al directorio del backend
cd /home/u268804017/domains/back.contigo-voy.com/public_html

echo "📁 Directorio actual: $(pwd)"

# Hacer backup de archivos críticos
echo "📦 Creando backup de archivos críticos..."
cp app/Http/Controllers/Blog/BlogController.php app/Http/Controllers/Blog/BlogController.php.backup
cp app/Models/Blog.php app/Models/Blog.php.backup
cp app/Http/Requests/PostBlogs/PostBlogs.php app/Http/Requests/PostBlogs/PostBlogs.php.backup

echo "✅ Backup creado exitosamente"

# Aplicar los cambios que hemos hecho en local
echo "🔄 Para aplicar los cambios, sigue estos pasos:"
echo ""
echo "1. Sube los archivos modificados desde tu local:"
echo "   - app/Http/Controllers/Blog/BlogController.php"
echo "   - app/Models/Blog.php"
echo "   - app/Http/Requests/PostBlogs/PostBlogs.php"
echo ""
echo "2. Ejecuta el script de corrección de slugs:"
echo "   php fix_blog_slugs.php"
echo ""
echo "3. Limpia el cache de Laravel:"
echo "   php artisan cache:clear"
echo "   php artisan config:clear"
echo "   php artisan route:clear"
echo ""
echo "4. Ejecuta el script de verificación:"
echo "   php verify_blogs.php"

echo ""
echo "🔍 Verificando blogs problemáticos actuales..."

# Ejecutar consulta para ver blogs sin slug
php -r "
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';
use App\Models\Blog;

echo 'Blogs sin slug:' . PHP_EOL;
\$blogs = Blog::whereNull('slug')->orWhere('slug', '')->get();
echo 'Total: ' . \$blogs->count() . PHP_EOL;

foreach(\$blogs as \$blog) {
    echo 'ID: ' . \$blog->idBlog . ' - Tema: ' . \$blog->tema . PHP_EOL;
}

echo PHP_EOL . 'Verificando títulos problemáticos...' . PHP_EOL;

\$problematicTitles = [
    '¿Amor o dependencia? Aprende a reconocer la diferencia',
    'Resiliencia emocional: cómo superar la adversidad',
    'Crianza positiva: estrategias para educar con amor y firmeza',
    'Cómo manejar la impulsividad: estrategias para tomar decisiones más conscientes',
    'El impacto de las redes sociales en la autoestima de los jóvenes',
    'Depresión vs. tristeza: diferencias clave que todos debemos conocer',
    'Mindfulness: una herramienta práctica para reducir el estrés diario'
];

foreach(\$problematicTitles as \$title) {
    \$blog = Blog::where('tema', 'LIKE', '%' . \$title . '%')->first();
    if(\$blog) {
        echo 'Encontrado - ID: ' . \$blog->idBlog . ' - Slug: ' . (\$blog->slug ?: 'NULL') . ' - Tema: ' . \$blog->tema . PHP_EOL;
    } else {
        echo 'NO ENCONTRADO: ' . \$title . PHP_EOL;
    }
}
"
