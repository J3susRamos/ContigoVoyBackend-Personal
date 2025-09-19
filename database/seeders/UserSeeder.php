<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Juan',
            'apellido' => 'Vasquez',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'fecha_nacimiento' => '2003-10-15',
            'imagen' => 'http:algo', 
            'rol' => 'ADMIN', 
        ]);

        $user->assignRole('ADMIN');
    }
}
