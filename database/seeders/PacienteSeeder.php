<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Paciente;
use App\Models\Psicologo;

class PacienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pacientes = [
            [
                "nombre" => "German",
                "apellido" => "Ames",
                "email" => "german@gmail.com",
                "fecha_nacimiento" => "2003-10-15",
                "imagen" => "http:algo",
                "ocupacion" => "Ingeniero",
                "estadoCivil" => "Soltero",
                "genero" => "Masculino",
                "DNI" => "12305678",
                "celular" => "987654321",
                "direccion" => "Av. Siempre Viva 742",
                "idPsicologo" => 1,
                "pais" => "Perú",
                "departamento" => "Lima",
            ],
            [
                "nombre" => "Jae",
                "apellido" => "Doe",
                "email" => "jaedoe@gmail.com",
                "fecha_nacimiento" => "2003-10-15",
                "imagen" => "http:algo",
                "ocupacion" => "Doctora",
                "estadoCivil" => "Casado",
                "genero" => "Femenino",
                "DNI" => "81654321",
                "celular" => "902345678",
                "direccion" => "Calle Falsa 123",
                "idPsicologo" => 1,
                "pais" => "Perú",
                "departamento" => "Lima",
            ],
            [
                "nombre" => "Alex",
                "apellido" => "Cuesta",
                "email" => "alexc@gmail.com",
                "fecha_nacimiento" => "2003-10-15",
                "imagen" => "http:algo",
                "ocupacion" => "Ingeniero",
                "estadoCivil" => "Soltero",
                "genero" => "Masculino",
                "DNI" => "12345678",
                "celular" => "983654321",
                "direccion" => "Av. Siempre Viva 742",
                "idPsicologo" => 1,
                "pais" => "Perú",
                "departamento" => "Lima",
            ],
            [
                "nombre" => "Jae",
                "apellido" => "Doe",
                "email" => "josefina@gmail.com",
                "fecha_nacimiento" => "2003-10-15",
                "imagen" => "http:algo",
                "ocupacion" => "Obstetra",
                "estadoCivil" => "Casado",
                "genero" => "Femenino",
                "DNI" => "87354321",
                "celular" => "912345670",
                "direccion" => "Calle Falsa 123",
                "idPsicologo" => 1,
                "pais" => "Perú",
                "departamento" => "Lima",
            ],
        ];

        foreach ($pacientes as $index => $paciente) {
            $paciente["codigo"] =
                "PAC" . str_pad($index + 1, 4, "0", STR_PAD_LEFT);
            Paciente::create($paciente);
        }
    }
}
