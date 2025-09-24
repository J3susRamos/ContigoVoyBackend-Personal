<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppService
{
    private $baseUrl;
    private $token;
    private $timeout;

    public function __construct()
    {
        $this->baseUrl = config(
            "services.whatsapp_service.base_url",
            "http://localhost:5111",
        );
        $this->token = config("services.whatsapp_service.token");
        $this->timeout = config("services.whatsapp_service.timeout", 30);

        if (!$this->token) {
            Log::error("WhatsApp Service token not configured");
            throw new Exception("WhatsApp Service token not configured");
        }
    }

    /**
     * Enviar mensaje de texto simple
     */
    public function sendTextMessage(string $to, string $message): array
    {
        $url = "{$this->baseUrl}/api/send-message";

        $payload = [
            "phone" => $this->formatPhoneNumber($to),
            "templateOption" => "custom",
            "psicologo" => "",
            "fecha" => "",
            "hora" => "",
            "customMessage" => $message,
        ];

        return $this->makeRequest($url, $payload, "POST");
    }

    /**
     * Enviar mensaje con template para citas
     */
    public function sendAppointmentMessage(
        string $to,
        string $psicologo,
        string $fecha,
        string $hora,
        string $templateOption = "confirmation",
    ): array {
        $url = "{$this->baseUrl}/api/send-message";

        $payload = [
            "phone" => $this->formatPhoneNumber($to),
            "templateOption" => $templateOption,
            "psicologo" => $psicologo,
            "fecha" => $fecha,
            "hora" => $hora,
        ];

        return $this->makeRequest($url, $payload, "POST");
    }

    /**
     * Enviar mensaje de confirmación de cita
     */
    public function sendConfirmationMessage(
        string $to,
        string $psicologo,
        string $fecha,
        string $hora,
    ): array {
        return $this->sendAppointmentMessage(
            $to,
            $psicologo,
            $fecha,
            $hora,
            "confirmation",
        );
    }

    /**
     * Enviar mensaje de recordatorio de cita
     */
    public function sendReminderMessage(
        string $to,
        string $psicologo,
        string $fecha,
        string $hora,
    ): array {
        return $this->sendAppointmentMessage(
            $to,
            $psicologo,
            $fecha,
            $hora,
            "reminder",
        );
    }

    /**
     * Enviar mensaje de cancelación de cita
     */
    public function sendCancellationMessage(
        string $to,
        string $psicologo,
        string $fecha,
        string $hora,
    ): array {
        return $this->sendAppointmentMessage(
            $to,
            $psicologo,
            $fecha,
            $hora,
            "cancellation",
        );
    }

    /**
     * Enviar mensaje de aceptación personalizado
     */
    public function sendAcceptMessage(string $to, string $message): array
    {
        $url = "{$this->baseUrl}/api/send-message-accept";

        $payload = [
            "phone" => $this->formatPhoneNumber($to),
            "message" => $message,
        ];

        return $this->makeRequest($url, $payload, "POST");
    }

    /**
     * Enviar mensaje de rechazo personalizado
     */
    public function sendRejectMessage(string $to, string $message): array
    {
        $url = "{$this->baseUrl}/api/send-message-reject";

        $payload = [
            "phone" => $this->formatPhoneNumber($to),
            "message" => $message,
        ];

        return $this->makeRequest($url, $payload, "POST");
    }

    /**
     * Enviar mensaje con imagen
     */
    public function sendImageMessage(
        string $to,
        string $imagePath,
        string $caption = "",
    ): array {
        $url = "{$this->baseUrl}/api/send-image";

        // Convertir imagen a base64 si es un archivo local
        $imageData = null;
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath);
        } else {
            return [
                "success" => false,
                "error" => "Archivo de imagen no encontrado",
            ];
        }

        $payload = [
            "phone" => $this->formatPhoneNumber($to),
            "imageData" => $imageData,
            "caption" => $caption,
            "mimeType" => $mimeType,
        ];

        return $this->makeRequest($url, $payload, "POST");
    }

    /**
     * Obtener estado de conexión del servicio WhatsApp
     */
    public function getConnectionStatus(): array
    {
        $url = "{$this->baseUrl}/api/status";
        return $this->makeRequest($url, [], "GET");
    }

    /**
     * Obtener estado del QR
     */
    public function getQRStatus(): array
    {
        $url = "{$this->baseUrl}/api/qr-status";
        return $this->makeRequest($url, [], "GET");
    }

    /**
     * Obtener código QR para autenticación
     */
    public function getQRCode(): array
    {
        $url = "{$this->baseUrl}/api/qr-code";
        return $this->makeRequest($url, [], "GET");
    }

    /**
     * Solicitar nuevo código QR
     */
    public function requestNewQR(): array
    {
        $url = "{$this->baseUrl}/api/qr-request";
        return $this->makeRequest($url, [], "POST");
    }

    /**
     * Forzar reconexión
     */
    public function forceReconnect(): array
    {
        $url = "{$this->baseUrl}/api/force-reconnect";
        return $this->makeRequest($url, [], "POST");
    }

    /**
     * Obtener mensajes enviados
     */
    public function getSentMessages(): array
    {
        $url = "{$this->baseUrl}/api/sent-messages";
        return $this->makeRequest($url, [], "GET");
    }

    /**
     * Resetear autenticación
     */
    public function resetAuth(): array
    {
        $url = "{$this->baseUrl}/api/auth/reset";
        return $this->makeRequest($url, [], "POST");
    }

    /**
     * Verificar si el servicio está conectado
     */
    public function isConnected(): bool
    {
        try {
            $status = $this->getConnectionStatus();
            return $status["success"] && ($status["connected"] ?? false);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Formatear número de teléfono para Perú
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remover caracteres no numéricos
        $phone = preg_replace("/[^\d]/", "", $phone);

        // Si tiene 9 dígitos, agregar código de país de Perú (51)
        if (strlen($phone) === 9) {
            $phone = "51" . $phone;
        }

        // Si no tiene código de país y tiene más de 9 dígitos, asumir que ya lo tiene
        return $phone;
    }

    /**
     * Realizar petición HTTP al servicio WhatsApp
     */
    private function makeRequest(
        string $url,
        array $payload,
        string $method = "POST",
    ): array {
        try {
            $httpClient = Http::withHeaders([
                "Authorization" => "Bearer " . $this->token,
                "Content-Type" => "application/json",
                "Accept" => "application/json",
            ])->timeout($this->timeout);

            if ($method === "GET") {
                $response = $httpClient->get($url, $payload);
            } else {
                $response = $httpClient->post($url, $payload);
            }

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info("WhatsApp message sent successfully", [
                    "url" => $url,
                    "method" => $method,
                    "phone" => $payload["phone"] ?? null,
                    "response" => $responseData,
                ]);

                return [
                    "success" => true,
                    "data" => $responseData,
                    "message_id" => $responseData["messageId"] ?? null,
                    "status" => "sent",
                ];
            }

            Log::error("WhatsApp Service API error", [
                "url" => $url,
                "method" => $method,
                "status" => $response->status(),
                "response" => $responseData,
                "payload" => $payload,
            ]);

            return [
                "success" => false,
                "error" =>
                    $responseData["message"] ??
                    "Error desconocido del servicio WhatsApp",
                "error_code" => $response->status(),
                "error_details" => $responseData,
            ];
        } catch (Exception $e) {
            Log::error("WhatsApp service connection error", [
                "url" => $url,
                "method" => $method,
                "error" => $e->getMessage(),
                "payload" => $payload,
            ]);

            return [
                "success" => false,
                "error" =>
                    "Error de conexión con el servicio WhatsApp: " .
                    $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener información del perfil del servicio
     */
    public function getBusinessProfile(): array
    {
        // El servicio de Baileys no maneja perfiles de business de la misma manera
        // Retornamos el estado de conexión como alternativa
        $status = $this->getConnectionStatus();

        if ($status["success"]) {
            return [
                "success" => true,
                "data" => [
                    "display_phone_number" => "WhatsApp Service Connected",
                    "verified_name" => "Contigo Voy WhatsApp",
                    "quality_rating" => "GREEN",
                    "connection_status" => $status,
                ],
            ];
        }

        return [
            "success" => false,
            "error" => "Servicio WhatsApp no disponible",
        ];
    }

    /**
     * Enviar mensaje usando templates predefinidos
     */
    public function sendTemplateMessage(
        string $to,
        string $templateName,
        array $parameters = [],
    ): array {
        // Mapear templates del Business API a nuestros templates personalizados
        switch ($templateName) {
            case "appointment_confirmation":
                return $this->sendConfirmationMessage(
                    $to,
                    $parameters[0] ?? "",
                    $parameters[1] ?? "",
                    $parameters[2] ?? "",
                );

            case "appointment_reminder":
                return $this->sendReminderMessage(
                    $to,
                    $parameters[0] ?? "",
                    $parameters[1] ?? "",
                    $parameters[2] ?? "",
                );

            case "appointment_cancellation":
                return $this->sendCancellationMessage(
                    $to,
                    $parameters[0] ?? "",
                    $parameters[1] ?? "",
                    $parameters[2] ?? "",
                );

            default:
                // Para templates no reconocidos, enviar como mensaje de texto
                $message = $templateName;
                if (!empty($parameters)) {
                    $message .= ": " . implode(", ", $parameters);
                }
                return $this->sendTextMessage($to, $message);
        }
    }

    /**
     * Enviar mensaje con opciones de botones
     * (Convertido a mensaje de texto con opciones numeradas)
     */
    public function sendButtonMessage(
        string $to,
        string $body,
        array $buttons,
    ): array {
        // Convertir botones a mensaje de texto con opciones numeradas
        $message = $body . "\n\nOpciones:\n";
        foreach ($buttons as $index => $button) {
            $message .= $index + 1 . ". " . $button["title"] . "\n";
        }

        return $this->sendTextMessage($to, $message);
    }

    /**
     * Enviar mensaje con lista de opciones
     * (Convertido a mensaje de texto con secciones)
     */
    public function sendListMessage(
        string $to,
        string $body,
        string $buttonText,
        array $sections,
    ): array {
        $message = $body . "\n\n" . $buttonText . ":\n";

        foreach ($sections as $section) {
            if (isset($section["title"])) {
                $message .= "\n📋 " . $section["title"] . ":\n";
            }

            if (isset($section["rows"])) {
                foreach ($section["rows"] as $index => $row) {
                    $message .= $index + 1 . ". " . $row["title"];
                    if (isset($row["description"])) {
                        $message .= " - " . $row["description"];
                    }
                    $message .= "\n";
                }
            }
        }

        return $this->sendTextMessage($to, $message);
    }
}
