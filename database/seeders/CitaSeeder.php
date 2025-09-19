<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cita;

class CitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $citas = [
            [
                'idEtiqueta' => 1,
                'idTipoCita' => 1,
                'idCanal' => 1,
                'idPaciente' => 1,
                'idPsicologo' => 1,
                'colores' => '#FF5733',
                'fecha_cita' => '2025-04-01',
                'duracion' => 60,
                'hora_cita' => '10:00:00',
                'motivo_Consulta' => 'Ansiedad y estrés.',
                'estado_Cita' => 'Pendiente',
            ],
            [
                'idEtiqueta' => 2,
                'idTipoCita' => 2,
                'idCanal' => 2,
                'idPaciente' => 2,
                'idPsicologo' => 2,
                'colores' => '#33FF57',
                'fecha_cita' => '2025-04-02',
                'duracion' => 45,
                'hora_cita' => '14:30:00',
                'motivo_Consulta' => 'Depresión leve.',
                'estado_Cita' => 'Confirmada',
            ],
            [
                'idEtiqueta' => 3,
                'idTipoCita' => 1,
                'idCanal' => 1,
                'idPaciente' => 3,
                'idPsicologo' => 1,
                'colores' => '#3357FF',
                'fecha_cita' => '2025-04-03',
                'duracion' => 30,
                'hora_cita' => '16:00:00',
                'motivo_Consulta' => 'Problemas de sueño.',
                'estado_Cita' => 'Cancelada',
            ]
        ];

        foreach ($citas as $data) {
            Cita::create($data);
        }
    }
}
