<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            AssignPermissionsToRolesSeeder::class,
            UserSeeder::class,
            CategoriaSeeder::class,
            EspecialidadSeeder::class,
            IdiomaSeeder::class,         
            PsicologoSeeder::class,       
            BlogSeeder::class, 
            CanalSeeder::class,
            TipoCitaSeeder::class,
            EtiquetaSeeder::class,
            ComentarioSeeder::class,
            PrePacienteSeeder::class,
            EnfermedadesSeeder::class, 
            PacienteSeeder::class,
            CitaSeeder::class,
            UrlsSeeder::class,
        ]);
    }
}
