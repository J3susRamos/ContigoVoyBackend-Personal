<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutomatedNotificationService;
use Carbon\Carbon;

class ProcessNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "notifications:process {--dry-run : Show what would be processed without sending}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Procesa y envía notificaciones automáticas de WhatsApp para citas";

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
        $this->info(
            "🚀 Iniciando procesamiento de notificaciones automáticas...",
        );
        $this->info("⏰ " . Carbon::now()->format("Y-m-d H:i:s"));

        $dryRun = $this->option("dry-run");

        if ($dryRun) {
            $this->warn("🧪 MODO DRY-RUN: No se enviarán mensajes reales");
        }

        try {
            if (!$dryRun) {
                $this->notificationService->procesarNotificacionesPendientes();
            }

            // Mostrar estadísticas
            $stats = $this->notificationService->obtenerEstadisticas(
                Carbon::today(),
                Carbon::tomorrow(),
            );

            $this->displayStats($stats);

            $this->info("✅ Procesamiento completado exitosamente");
        } catch (\Exception $e) {
            $this->error(
                "❌ Error durante el procesamiento: " . $e->getMessage(),
            );
            return 1;
        }

        return 0;
    }

    private function displayStats(array $stats)
    {
        $this->info("📊 Estadísticas del día:");
        $this->table(
            ["Métrica", "Cantidad"],
            [
                ["Total notificaciones", $stats["total"]],
                ["Enviadas", $stats["enviadas"]],
                ["Pendientes", $stats["pendientes"]],
                ["Errores", $stats["errores"]],
            ],
        );

        if (!empty($stats["por_tipo"])) {
            $this->info("📋 Por tipo de notificación:");
            $tipoData = [];
            foreach ($stats["por_tipo"] as $tipo => $cantidad) {
                $tipoData[] = [$tipo, $cantidad];
            }
            $this->table(["Tipo", "Cantidad"], $tipoData);
        }
    }
}
