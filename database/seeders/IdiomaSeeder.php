<?php

namespace Database\Seeders;
use App\Models\Idioma;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IdiomaSeeder extends Seeder
{
   private function norm(string $s): string
    {
        $s = trim($s);
        $s = mb_strtolower($s, 'UTF-8');
        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }

    public function run(): void
    {
        $idiomas = [
            'Español',
            'Inglés',
            'Francés',
            'Alemán',
            'Italiano',
            'Portugués',
            'Quechua',
            'Aymara',
            'Chino',
            'Japonés',
        ];

        foreach ($idiomas as $nombre) {
            $nombre = $this->norm($nombre);
            Idioma::firstOrCreate(['nombre' => $nombre]);
        }
    }
}