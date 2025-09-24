<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;

class WhatsAppServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:service
                            {--status : Verificar estado del servicio}
                            {--qr : Mostrar información del código QR}
                            {--test : Enviar mensaje de prueba}
                            {--reconnect : Forzar reconexión del servicio}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Gestiona y verifica el WhatsApp Service (Baileys)";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🚀 WhatsApp Service - Gestión y Verificación");
        $this->newLine();

        // Verificar configuración básica
        $this->checkConfiguration();

        $status = $this->option("status");
        $qr = $this->option("qr");
        $test = $this->option("test");
        $reconnect = $this->option("reconnect");

        if ($status) {
            $this->checkServiceStatus();
        } elseif ($qr) {
            $this->showQrInformation();
        } elseif ($test) {
            $this->testService();
        } elseif ($reconnect) {
            $this->forceReconnect();
        } else {
            $this->showServiceOverview();
        }
    }

    /**
     * Verificar configuración del servicio
     */
    private function checkConfiguration(): bool
    {
        $this->info("🔍 Verificando configuración...");

        $baseUrl = config("services.whatsapp_service.base_url");
        $token = config("services.whatsapp_service.token");
        $timeout = config("services.whatsapp_service.timeout", 30);

        if (!$token) {
            $this->error("❌ WHATSAPP_SERVICE_TOKEN no configurado en .env");
            $this->line("Agrega: WHATSAPP_SERVICE_TOKEN=tu_token_aqui");
            return false;
        }

        if (!$baseUrl) {
            $this->warn(
                "⚠️  URL del servicio usando valor por defecto: http://localhost:5111",
            );
        } else {
            $this->info("✅ URL del servicio: {$baseUrl}");
        }

        $this->info("✅ Token configurado");
        $this->info("✅ Timeout: {$timeout}s");

        return true;
    }

    /**
     * Verificar estado del servicio
     */
    private function checkServiceStatus()
    {
        $this->info("📊 Verificando estado del servicio...");
        $this->newLine();

        try {
            $whatsappService = app(WhatsAppService::class);

            // Verificar conexión
            $status = $whatsappService->getConnectionStatus();

            if ($status["success"]) {
                $this->info("✅ Servicio WhatsApp: CONECTADO");

                if (
                    isset($status["data"]["connected"]) &&
                    $status["data"]["connected"]
                ) {
                    $this->info("✅ WhatsApp Web: AUTENTICADO");
                } else {
                    $this->warn("⚠️  WhatsApp Web: DESCONECTADO");
                    $this->line(
                        "Necesitas escanear el código QR para autenticar.",
                    );
                }

                // Mostrar información adicional si está disponible
                if (isset($status["data"])) {
                    $data = $status["data"];
                    if (isset($data["phone"])) {
                        $this->info("📱 Teléfono: {$data["phone"]}");
                    }
                    if (isset($data["battery"])) {
                        $this->info("🔋 Batería: {$data["battery"]}%");
                    }
                }
            } else {
                $this->error("❌ Servicio WhatsApp: DESCONECTADO");
                $this->line(
                    "Error: " . ($status["error"] ?? "Error desconocido"),
                );
            }

            // Verificar estado del QR
            $qrStatus = $whatsappService->getQRStatus();
            if ($qrStatus["success"]) {
                $qrData = $qrStatus["data"] ?? [];
                $this->info(
                    "📱 Estado QR: " . ($qrData["status"] ?? "Desconocido"),
                );
            }
        } catch (\Exception $e) {
            $this->error("❌ Error conectando al servicio: {$e->getMessage()}");
        }
    }

    /**
     * Mostrar información del código QR
     */
    private function showQrInformation()
    {
        $this->info("📱 Información de autenticación QR:");
        $this->newLine();

        try {
            $whatsappService = app(WhatsAppService::class);
            $qrStatus = $whatsappService->getQRStatus();

            if ($qrStatus["success"]) {
                $qrData = $qrStatus["data"] ?? [];
                $status = $qrData["status"] ?? "unknown";

                switch ($status) {
                    case "connected":
                        $this->info(
                            "✅ WhatsApp ya está conectado y autenticado",
                        );
                        break;

                    case "qr_ready":
                        $this->warn("📱 Código QR disponible para escanear");
                        $baseUrl = config(
                            "services.whatsapp_service.base_url",
                            "http://localhost:5111",
                        );
                        $this->line("URL del QR: {$baseUrl}/api/qr-code");
                        break;

                    case "connecting":
                        $this->info("🔄 Conectando a WhatsApp...");
                        break;

                    default:
                        $this->warn("⚠️  Estado del QR: {$status}");
                        break;
                }
            } else {
                $this->error("❌ No se pudo obtener el estado del QR");
                $this->line(
                    "Error: " . ($qrStatus["error"] ?? "Error desconocido"),
                );
            }

            // Instrucciones generales
            $this->newLine();
            $this->line("📋 Instrucciones:");
            $this->line("1. Abre WhatsApp en tu teléfono");
            $this->line("2. Ve a Configuración > Dispositivos vinculados");
            $this->line("3. Toca 'Vincular un dispositivo'");
            $this->line("4. Escanea el código QR mostrado en la URL");
        } catch (\Exception $e) {
            $this->error(
                "❌ Error obteniendo información del QR: {$e->getMessage()}",
            );
        }
    }

    /**
     * Probar el servicio enviando un mensaje de prueba
     */
    private function testService()
    {
        $this->info("🧪 Probando servicio WhatsApp...");

        // Solicitar número de teléfono para prueba
        $phone = $this->ask(
            "Ingresa un número de teléfono para prueba (incluye código de país)",
        );

        if (!$phone) {
            $this->error("❌ Número de teléfono requerido");
            return;
        }

        try {
            $whatsappService = app(WhatsAppService::class);

            $testMessage =
                "🧪 Mensaje de prueba desde WhatsApp Service\n" .
                "Fecha: " .
                now()->format("d/m/Y H:i:s") .
                "\n" .
                "Este es un mensaje de prueba para verificar la conectividad.";

            $this->info("📤 Enviando mensaje de prueba...");

            $result = $whatsappService->sendTextMessage($phone, $testMessage);

            if ($result["success"]) {
                $this->info("✅ Mensaje enviado correctamente");
                if (isset($result["message_id"])) {
                    $this->line("ID del mensaje: {$result["message_id"]}");
                }
            } else {
                $this->error("❌ Error enviando mensaje");
                $this->line(
                    "Error: " . ($result["error"] ?? "Error desconocido"),
                );
            }
        } catch (\Exception $e) {
            $this->error("❌ Error en prueba del servicio: {$e->getMessage()}");
        }
    }

    /**
     * Forzar reconexión del servicio
     */
    private function forceReconnect()
    {
        $this->info("🔄 Forzando reconexión del servicio...");

        try {
            $whatsappService = app(WhatsAppService::class);

            $result = $whatsappService->forceReconnect();

            if ($result["success"]) {
                $this->info("✅ Reconexión iniciada correctamente");
                $this->line(
                    "El servicio intentará reconectarse automáticamente.",
                );
                $this->warn(
                    "⚠️  Puede que necesites escanear un nuevo código QR.",
                );
            } else {
                $this->error("❌ Error forzando reconexión");
                $this->line(
                    "Error: " . ($result["error"] ?? "Error desconocido"),
                );
            }
        } catch (\Exception $e) {
            $this->error("❌ Error en reconexión: {$e->getMessage()}");
        }
    }

    /**
     * Mostrar resumen general del servicio
     */
    private function showServiceOverview()
    {
        $this->info("📋 Resumen del WhatsApp Service:");
        $this->newLine();

        // Mostrar configuración
        $baseUrl = config(
            "services.whatsapp_service.base_url",
            "http://localhost:5111",
        );
        $timeout = config("services.whatsapp_service.timeout", 30);

        $this->line("🌐 URL del servicio: {$baseUrl}");
        $this->line("⏱️  Timeout: {$timeout}s");
        $this->newLine();

        // Verificar estado rápido
        try {
            $whatsappService = app(WhatsAppService::class);
            $status = $whatsappService->getConnectionStatus();

            if ($status["success"]) {
                $this->info("✅ Servicio: OPERATIVO");
            } else {
                $this->error("❌ Servicio: NO DISPONIBLE");
            }
        } catch (\Exception $e) {
            $this->error("❌ Servicio: ERROR DE CONEXIÓN");
        }

        $this->newLine();
        $this->line("💡 Comandos disponibles:");
        $this->line(
            "  php artisan whatsapp:service --status     # Ver estado detallado",
        );
        $this->line(
            "  php artisan whatsapp:service --qr         # Información del QR",
        );
        $this->line(
            "  php artisan whatsapp:service --test       # Enviar mensaje de prueba",
        );
        $this->line(
            "  php artisan whatsapp:service --reconnect  # Forzar reconexión",
        );
    }
}
