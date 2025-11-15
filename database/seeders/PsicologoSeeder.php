<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Psicologo;
use App\Models\Idioma;
use Illuminate\Support\Str;


class PsicologoSeeder extends Seeder
{
    private function norm(string $s): string
    {
    $s = trim($s);
    $s = mb_strtolower($s, 'UTF-8');
    return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }
    public function run(): void
    {
        $psicologos = [
            [
                'titulo' => 'Pedagogo',
                'name' => 'Luis',
                'apellido' => 'Gonzales',
                'email' => 'luisgonzales@gmail.com',
                'fecha_nacimiento' => '2003-05-01',
                'imagen' => 'https:algo',
                'password' => 'password123',
                'pais' => 'MX',
                'experiencia' => 5,
                'genero' => 'masculino',
                'introduccion' => 'Soy Luis, profesional en psicología...',
                'horario' => [
                    "Lunes" => [["09:00", "12:00"]],
                    "Martes" => [["10:00", "14:00"]],
                    "Jueves" => [["13:00", "17:00"]]
                ],
                'especialidad' => 1,
                'idiomas' => ['Español', 'Inglés']
               

            ],
            [
                'titulo' => 'Psicoanalista',
                'name' => 'Maria',
                'apellido' => 'Fernandez',
                'email' => 'mariafernandez@gmail.com',
                'fecha_nacimiento' => '1996-07-12',
                'imagen' => 'https:algo',
                'password' => 'password123',
                'pais' => 'AR',
                'experiencia' => 7,
                'genero' => 'femenino',
                'introduccion' => 'Soy Maria, especialista en terapia cognitiva...',
                'horario' => [
                    "Martes" => [["08:00", "12:00"]],
                    "Miercoles" => [["14:00", "18:00"]],
                    "Viernes" => [["10:00", "15:00"]]
                ],
                'especialidad' => 2,
                'idiomas' => ['Español', 'Francés']
            ],
            [
                'titulo' => 'Terapeuta',
                'name' => 'Carlos',
                'apellido' => 'Ramirez',
                'email' => 'carlosramirez@gmail.com',
                'fecha_nacimiento' => '1984-11-25',
                'imagen' => 'https:algo',
                'password' => 'password123',
                'pais' => 'CO',
                'experiencia' => 10,
                'genero' => 'masculino',
                'introduccion' => 'Soy Carlos, con experiencia en psicología clínica...',
                'horario' => [
                    "Lunes" => [["07:00", "11:00"]],
                    "Miercoles" => [["09:00", "13:00"]],
                    "Jueves" => [["15:00", "19:00"]]
                ],
                'especialidad' => 3,
                'idiomas' => ['Francés'],

            ],
            [
                'titulo' => 'Pediatra',
                'name' => 'Ana',
                'apellido' => 'Lopez',
                'email' => 'analopez@gmail.com',
                'fecha_nacimiento' => '1989-03-18',
                'imagen' => 'https:algo',
                'password' => 'password123',
                'pais' => 'PE',
                'experiencia' => 8,
                'genero' => 'femenino',
                'introduccion' => 'Soy Ana, experta en salud mental y bienestar...',
                'horario' => [
                    "Martes" => [["09:00", "13:00"]],
                    "Jueves" => [["11:00", "16:00"]],
                    "Viernes" => [["14:00", "18:00"]]
                ],
                'especialidad' => 4,
                'idiomas' => ['Japonés', 'Francés', 'Español'],
            ],
            [
                'titulo' => 'Conductual',
                'name' => 'Javier',
                'apellido' => 'Hernandez',
                'email' => 'javierhernandez@gmail.com',
                'fecha_nacimiento' => '1986-09-21',
                'imagen' => 'https:algo',
                'password' => 'password123',
                'pais' => 'CL',
                'experiencia' => 9,
                'genero' => 'masculino',
                'introduccion' => 'Soy Javier, psicólogo con enfoque en terapias familiares...',
                'horario' => [
                    "Lunes" => [["08:00", "12:00"]],
                    "Miercoles" => [["13:00", "17:00"]],
                    "Viernes" => [["10:00", "14:00"]]
                ],
                'especialidad' => 5,
                'idiomas' => ['Español']
            ],
            [
                'titulo' => 'Pedadgogo',
                'name' => 'Elena',
                'apellido' => 'Castro',
                'email' => 'elenacastro@gmail.com',
                'fecha_nacimiento' => '1995-06-30',
                'imagen' => 'https:algo',
                'password' => 'password123',
                'pais' => 'EC',
                'experiencia' => 6,
                'genero' => 'femenino',
                'introduccion' => 'Soy Elena, especialista en psicología infantil...',
                'horario' => [
                    "Martes" => [["08:00", "12:00"]],
                    "Jueves" => [["10:00", "15:00"]],
                    "Sabado" => [["09:00", "13:00"]]
                ],
                'especialidad' => 2,
                'idiomas' => ['Español']
            ]
        ];

        foreach ($psicologos as $data) {
            $usuario = User::create([
                'name' => $data['name'],
                'apellido' => $data['apellido'],
                'email' => $data['email'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'imagen' => $data['imagen'],
                'password' => Hash::make($data['password']),
                'rol' => 'PSICOLOGO',
                'estado' => 1
            ]);
            
            $usuario->assignRole('PSICOLOGO');

            $psicologo = Psicologo::create([
                'titulo' => $data['titulo'],
                'user_id' => $usuario->user_id,
                'introduccion' => $data['introduccion'],
                'pais' => $data['pais'],
                'experiencia' => $data['experiencia'],
                'genero' => $data['genero'],
                'horario' => $data['horario'],
            ]);

            $psicologo->especialidades()->attach([$data['especialidad']]);
             $nombres = array_map(fn($n) => $this->norm($n), $data['idiomas'] ?? []);

                // Asegura que todos existen (si falta alguno, lo crea):
                 // (Si prefieres NO crear nuevos, puedes omitir este bloque y solo buscar.)
            $ids = [];
            foreach ($nombres as $nombre) {
                $idioma = Idioma::firstOrCreate(['nombre' => $nombre]);
                $ids[] = $idioma->idIdioma;
    }

    // Evita duplicados si corres el seeder varias veces:
    $psicologo->idiomas()->syncWithoutDetaching($ids);
        }
    }
}
