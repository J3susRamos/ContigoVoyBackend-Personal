<?php

namespace App\Jobs;

use App\Models\Cita;
use App\Mail\ConfirmacionPrePaciente;
use App\Mail\CitaPsicologo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EnviarConfirmacionCitaCorreo
{
    use Dispatchable;

    public Cita $cita;

    /**
     * Create a new job instance.
     */
    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Iniciando EnviarConfirmacionCitaCorreo', ['cita_id' => $this->cita->idCita]);
        try {
            // Cargar relaciones necesarias
            $this->cita->load('psicologo.users', 'prepaciente', 'paciente');

            // Obtener paciente (prepaciente o paciente normal)
            $paciente = $this->cita->prepaciente ?? $this->cita->paciente;

            if (!$paciente || !$paciente->correo) {
                Log::warning('No se pudo enviar correo - paciente o correo no disponible', [
                    'cita_id' => $this->cita->idCita ?? null,
                ]);
                return;
            }

            $nombre = $paciente->nombre;

            // Obtener datos del psicólogo
            $nombrePsicologo = 'su psicólogo asignado';
            if ($this->cita->psicologo && $this->cita->psicologo->users) {
                $nombrePsicologo = $this->cita->psicologo->users->name . ' ' . $this->cita->psicologo->users->apellido;
            }

            // Formatear fecha y hora
            $fecha = Carbon::parse($this->cita->fecha_cita)->format('Y-m-d');
            $hora = Carbon::parse($this->cita->hora_cita)->format('H:i');

            $motivo = $this->cita->motivo_Consulta;


            // Mapeo de enfoques
            $mapeoEnfoques = [
                'niños' => 'Pediatra',
                'adolescentes' => 'Pedagogo',
                'familiar' => 'Psicoanalista',
                'pareja' => 'Terapeuta',
                'adulto' => 'Conductual'
            ];

            // Obtener tipo de consulta desde la cita
            $tipoConsulta = $this->cita->tipo_consulta ?? null;

            // Determinar título del psicólogo según el tipo de consulta
            $tituloPsicologo = $mapeoEnfoques[$tipoConsulta] ?? $this->cita->psicologo->titulo ?? 'General';


            // Correo al admin (opcional)
            $adminEmail = config('emails.admin_address', 'contigovoyproject@gmail.com');
            Mail::to($adminEmail)->send(
                new ConfirmacionPrePaciente(
                    [
                        'nombre' => $nombre,
                        'fecha' => $fecha,
                        'hora' => $hora,
                        'psicologo' => $nombrePsicologo,
                    ],
                    $motivo,
                    $this->cita->jitsi_url
                )
            );

            // Correo al psicólogo
            $correoPsicologo = null;
            if ($this->cita->psicologo && $this->cita->psicologo->users) {
                $correoPsicologo = $this->cita->psicologo->users->email;
            }
            Log::info('Intentando enviar correo al psicólogo', [
                'cita_id' => $this->cita->idCita,
                'correoPsicologo' => $correoPsicologo,
                'psicologo_id' => $this->cita->psicologo ? $this->cita->psicologo->idPsicologo : null,
                'users_id' => $this->cita->psicologo && $this->cita->psicologo->users ? $this->cita->psicologo->users->id : null,
            ]);
            if ($correoPsicologo) {
                Mail::to($correoPsicologo)->send(
                    new CitaPsicologo([
                        'nombrePaciente' => $paciente->nombre,
                        'correoPaciente' => $paciente->correo,
                        'celularPaciente' => $paciente->celular,
                        'fecha' => $fecha,
                        'hora' => $hora,
                        'psicologo' => $nombrePsicologo,
                        'tituloPsicologo' => $tituloPsicologo,
                        'motivo' => $motivo,
                    ], $this->cita->jitsi_url)
                );
                Log::info('Correo enviado al psicólogo correctamente', ['cita_id' => $this->cita->idCita]);
            } else {
                Log::warning('No se pudo enviar correo al psicólogo: email no encontrado', ['cita_id' => $this->cita->idCita]);
            }
        } catch (\Exception $e) {
            Log::error('Error enviando confirmación de cita por correo', [
                'cita_id' => $this->cita->idCita ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job EnviarConfirmacionCitaCorreo falló definitivamente', [
            'cita_id' => $this->cita->idCita ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
