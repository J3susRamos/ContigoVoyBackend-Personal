<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            PsicologoSeeder::class,
            BlogSeeder::class, 
            CanalSeeder::class,
            TipoCitaSeeder::class,
            EtiquetaSeeder::class,
            ComentarioSeeder::class,
            PrePacienteSeeder::class,
            EnfermedadesSeeder::class, 
            PacienteSeeder::class,
            EnfermedadesSeeder::class, 
            CitaSeeder::class,
            UrlsSeeder::class,
            
        ]);

    }
}
