<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use firstOrCreate so seeding is idempotent and won't fail if roles already exist
        $roles = [
            'ADMIN',
            'PSICOLOGO',
            'PACIENTE',
            'MARKETING',
            'COMUNICACION' // Agregando este rol que vi en las rutas
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }
    }
}
