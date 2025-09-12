<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Blog;
use App\Models\Psicologo;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $blogs = [
            [
                'tema' => 'Los beneficios de la meditación en la salud mental',
                'contenido' => str_repeat('Este es un artículo sobre los beneficios de la meditación. ', 10),
                'imagenes' => 'https://ejemplo.com/imagen1.jpg',
                'idCategoria' => 1,
                'idPsicologo' => 1 
            ],
            [
                'tema' => 'Cómo manejar la ansiedad en tiempos de crisis',
                'contenido' => str_repeat('La ansiedad puede ser controlada con estos consejos. ', 10),
                'imagenes' => 'https://ejemplo.com/imagen2.jpg',
                'idCategoria' => 2,
                'idPsicologo' => 1 
            ],
            [
                'tema' => 'Importancia del sueño para la estabilidad emocional',
                'contenido' => str_repeat('Dormir bien es clave para la salud emocional. ', 10),
                'imagenes' => 'https://ejemplo.com/imagen3.jpg',
                'idCategoria' => 3,
                'idPsicologo' => 1 
            ]
        ];

        foreach ($blogs as &$blog) {
            $blog['slug'] = Str::slug($blog['tema']);
        }

        Blog::insert($blogs);
    }
}
