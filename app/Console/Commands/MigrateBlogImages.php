<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Blog;
use Illuminate\Support\Facades\DB;

class MigrateBlogImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:migrate-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar imágenes existentes de blogs de formato único a array múltiple';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migración de imágenes de blogs...');

        try {
            // Obtener todos los blogs que aún tienen datos en formato de imagen única
            $blogs = DB::table('blogs')
                ->whereNotNull('imagenes')
                ->where('imagenes', '!=', '')
                ->get();

            $migrated = 0;
            $errors = 0;

            foreach ($blogs as $blog) {
                try {
                    // Verificar si ya es un array JSON válido
                    $decoded = json_decode($blog->imagenes, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        // Ya está en formato array, saltar
                        continue;
                    }

                    // Si no es un array, convertir la imagen única a array
                    $imageArray = [$blog->imagenes];

                    // Actualizar el blog con el nuevo formato
                    DB::table('blogs')
                        ->where('idBlog', $blog->idBlog)
                        ->update([
                            'imagenes' => json_encode($imageArray)
                        ]);

                    $migrated++;
                    $this->line("Blog ID {$blog->idBlog} migrado correctamente");

                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Error migrando Blog ID {$blog->idBlog}: " . $e->getMessage());
                }
            }

            $this->info("Migración completada:");
            $this->info("- Blogs migrados: {$migrated}");
            $this->info("- Errores: {$errors}");

            if ($errors === 0) {
                $this->info("✅ Migración exitosa!");
            } else {
                $this->warn("⚠️  Migración completada con algunos errores.");
            }

        } catch (\Exception $e) {
            $this->error("Error general durante la migración: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
