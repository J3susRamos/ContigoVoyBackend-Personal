<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutomatedNotificationService;
use App\Models\Cita;
use Carbon\Carbon;

class ScheduleNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "notifications:schedule {--days=7 : Number of days ahead to schedule notifications} {--force : Force reschedule existing notifications}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Programa notificaciones automáticas para citas futuras";

    private $notificationService;

    public function __construct(
        AutomatedNotificationService $notificationService,
    ) {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("📅 Programando notificaciones para citas futuras...");

        $days = (int) $this->option("days");
        $force = $this->option("force");

        $fechaInicio = Carbon::now();
        $fechaFin = Carbon::now()->addDays($days);

        $this->info(
            "📍 Rango: {$fechaInicio->format("Y-m-d")} a {$fechaFin->format(
                "Y-m-d",
            )}",
        );

        // Obtener citas futuras que necesitan notificaciones programadas
        $citas = Cita::whereBetween("fecha_cita", [
            $fechaInicio->format("Y-m-d"),
            $fechaFin->format("Y-m-d"),
        ])
            ->whereNotIn("estado_Cita", ["Cancelada", "Confirmada"])
            ->with(["paciente", "prepaciente", "psicologo.users"])
            ->get();

        $this->info("🔍 Encontradas {$citas->count()} citas para programar");

        $programadas = 0;
        $omitidas = 0;

        foreach ($citas as $cita) {
            // Verificar si tiene teléfono
            $telefono = $cita->paciente
                ? $cita->paciente->celular
                : ($cita->prepaciente
                    ? $cita->prepaciente->celular
                    : null);

            if (!$telefono) {
                $this->warn("⚠️  Cita {$cita->idCita} omitida: Sin teléfono");
                $omitidas++;
                continue;
            }

            try {
                $resultado = $this->notificationService->programarNotificacionesPorCita(
                    $cita->idCita,
                );

                if ($resultado) {
                    $programadas++;
                    $this->info(
                        "✅ Cita {$cita->idCita} - {$cita->fecha_cita} {$cita->hora_cita}",
                    );
                } else {
                    $omitidas++;
                    $this->warn(
                        "⚠️  Cita {$cita->idCita} no se pudo programar",
                    );
                }
            } catch (\Exception $e) {
                $omitidas++;
                $this->error(
                    "❌ Error en cita {$cita->idCita}: {$e->getMessage()}",
                );
            }
        }

        $this->info("📊 Resumen:");
        $this->info("  • Citas programadas: {$programadas}");
        $this->info("  • Citas omitidas: {$omitidas}");
        $this->info("✅ Proceso completado");

        return 0;
    }
}
