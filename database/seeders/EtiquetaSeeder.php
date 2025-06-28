<?php

namespace Database\Seeders;

use App\Models\Etiqueta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EtiquetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $etiquetas = [
            ['nombre' => 'Importante'],
            ['nombre' => 'Urgente'],
            ['nombre' => 'Normal'],
        ];

        foreach ($etiquetas as $etiqueta) {
            Etiqueta::create($etiqueta);
        }
    }
}
