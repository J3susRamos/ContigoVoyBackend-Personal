<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comentario;

class ComentarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comentarios = [
            [
                'nombre' => 'Juan Pérez',
                'comentario' => 'Muy interesante el artículo, me ayudó mucho.',
                'idBlog' => 1,
            ],
            [
                'nombre' => 'Ana López',
                'comentario' => 'Gracias por compartir esta información.',
                'idBlog' => 1,
            ],
            [
                'nombre' => 'Carlos Ramírez',
                'comentario' => 'Voy a probar estos consejos, gracias!',
                'idBlog' => 2,
            ]
        ];

        foreach ($comentarios as $data) {
            Comentario::create($data);
        }
    }
}
