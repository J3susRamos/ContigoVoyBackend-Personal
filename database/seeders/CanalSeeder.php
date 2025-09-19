<?php

namespace Database\Seeders;

use App\Models\Canal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CanalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Canal::create(['nombre' => 'Cita online']);
        Canal::create(['nombre' => 'Marketing directo']);
        Canal::create(['nombre' => 'Referidos']);
    }
}
