<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UrlsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('urls')->insert([
            [
                'name' => 'Dashboard',
                'enlace' => '/user/home',
                'idPadre' => null,
                'iconoName' => 'icon-dashboard',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Citas',
                'enlace' => '',
                'idPadre' => null,
                'iconoName' => 'icon-citas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Citas sin Pagar',
                'enlace' => '/user/citas-sin-pagar',
                'idPadre' => 2,
                'iconoName' => 'icon-citas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Citas Pagadas',
                'enlace' => '/user/citas-pagadas',
                'idPadre' => 2,
                'iconoName' => 'icon-citas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Registro de personal',
                'enlace' => '/user/personal',
                'idPadre' => null,
                'iconoName' => 'icon-personal',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Pacientes',
                'enlace' => '/user/pacientes',
                'idPadre' => null,
                'iconoName' => 'icon-pacientes',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Psicologos',
                'enlace' => '/user/psicologos',
                'idPadre' => null,
                'iconoName' => 'icon-psicologos',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Citas',
                'enlace' => '/user/citas',
                'idPadre' => null,
                'iconoName' => 'icon-citas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Historial',
                'enlace' => '/user/historial',
                'idPadre' => null,
                'iconoName' => 'icon-historial',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Calendario',
                'enlace' => '/user/calendario',
                'idPadre' => null,
                'iconoName' => 'icon-calendario',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Estadisticas',
                'enlace' => '/user/estadisticas',
                'idPadre' => null,
                'iconoName' => 'icon-estadisticas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Blog',
                'enlace' => '/user/blog',
                'idPadre' => null,
                'iconoName' => 'icon-blog',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Marketing',
                'enlace' => '/user/marketing',
                'idPadre' => null,
                'iconoName' => 'icon-marketing',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Politicas y Privacidad',
                'enlace' => '/user/politicas',
                'idPadre' => null,
                'iconoName' => 'icon-politicas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
