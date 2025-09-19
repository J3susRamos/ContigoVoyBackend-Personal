<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EspecialidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $especialidades = [
            ['nombre' => 'Psicoanálisis'],
            ['nombre' => 'Gestalt humanista'],
            ['nombre' => 'Neuropsicología'],
            ['nombre' => 'Psicopedagogía'],
            ['nombre' => 'Cognitivo-conductual'],
            ['nombre' => 'Racional-emotivo-conductual'],
        ];

        foreach ($especialidades as $especialidad) {
            Especialidad::create($especialidad);
        }
    }
}
