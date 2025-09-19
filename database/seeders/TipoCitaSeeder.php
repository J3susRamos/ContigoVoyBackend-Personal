<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoCita;

class TipoCitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Presencial'],
            ['nombre' => 'Virtual'],
            ['nombre' => 'Domicilio'],
        ];

        foreach ($tipos as $tipo) {
            TipoCita::create($tipo);
        }
    }
}
