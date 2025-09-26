<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class TestWhatsAppMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test-message {phone} {--message=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar mensaje de prueba al número especificado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');
        $customMessage = $this->option('message');

        $this->info("🧪 Enviando mensaje de prueba a: {$phone}");
        $this->newLine();

        try {
            $whatsappService = app(WhatsAppService::class);

            $message = $customMessage ?:
                "🧪 Mensaje de prueba desde WhatsApp Service\n" .
                "Fecha: " . now()->format('d/m/Y H:i:s') . "\n" .
                "Este es un mensaje de prueba para verificar la conectividad.\n" .
                "¡Sistema funcionando correctamente! ✅";

            $this->info("📤 Enviando mensaje...");
            $this->line("Contenido: " . substr($message, 0, 100) . "...");

            $result = $whatsappService->sendTextMessage($phone, $message);

            if ($result['success']) {
                $this->info("✅ Mensaje enviado exitosamente");

                if (isset($result['message_id'])) {
                    $this->line("📧 ID del mensaje: {$result['message_id']}");
                }

                if (isset($result['data']['messageId'])) {
                    $this->line("📧 Message ID (data): {$result['data']['messageId']}");
                }

                $this->newLine();
                $this->info("🎉 Prueba completada con éxito");

                // Log para registro
                Log::info('WhatsApp test message sent successfully', [
                    'phone' => $phone,
                    'message_length' => strlen($message),
                    'result' => $result
                ]);

            } else {
                $this->error("❌ Error enviando mensaje");
                $this->line("Error: " . ($result['error'] ?? 'Error desconocido'));

                if (isset($result['error_details'])) {
                    $this->newLine();
                    $this->warn("Detalles del error:");
                    $this->line(json_encode($result['error_details'], JSON_PRETTY_PRINT));
                }

                // Log del error
                Log::error('WhatsApp test message failed', [
                    'phone' => $phone,
                    'error' => $result['error'] ?? 'Unknown error',
                    'result' => $result
                ]);

                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error("❌ Excepción durante el envío: {$e->getMessage()}");

            Log::error('WhatsApp test message exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
