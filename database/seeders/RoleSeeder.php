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
        Role::create(['name' => 'ADMIN', 'guard_name' => 'web']);
        
        Role::create(['name' => 'PSICOLOGO', 'guard_name' => 'web']);

        Role::create(['name' => 'PACIENTE', 'guard_name' => 'web']);

        Role::create(['name' => 'MARKETING', 'guard_name' => 'web']);
    }
}
