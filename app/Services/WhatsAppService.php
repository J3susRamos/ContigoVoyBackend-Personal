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
    private $username;
    private $password;
    private $tokenExpiration;

    public function __construct()
    {
        $this->timeout = config("services.whatsapp_service.timeout", 30);
        $this->baseUrl = env('WHATSAPP_SERVICE_URL');
        $this->username = env('WHATSAPP_SERVICE_USERNAME');
        $this->password = env('WHATSAPP_SERVICE_PASSWORD');

        // El token se obtendrá dinámicamente mediante login
        $this->token = null;
        $this->tokenExpiration = null;
    }

    /**
     * Realizar login y obtener token de autenticación
     */
    private function login(): bool
    {
        try {
            $loginUrl = "{$this->baseUrl}/api/auth/login";

            Log::info("WhatsApp Service: Intentando login", [
                "url" => $loginUrl,
                "username" => $this->username,
            ]);

            $response = Http::timeout($this->timeout)->post($loginUrl, [
                "username" => $this->username,
                "password" => $this->password,
            ]);

            Log::info("WhatsApp Service: Respuesta de login recibida", [
                "status_code" => $response->status(),
                "response_size" => strlen($response->body()),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info("WhatsApp Service: Datos de login", [
                    "has_token" => isset($data["token"]),
                    "has_expires_in" => isset($data["expires_in"]),
                    "response_keys" => array_keys($data),
                ]);

                if (isset($data["token"])) {
                    $this->token = $data["token"];
                    // Si hay información de expiración, la guardamos
                    if (isset($data["expires_in"])) {
                        $this->tokenExpiration = now()->addSeconds(
                            $data["expires_in"],
                        );
                    } else {
                        // Por defecto, asumir que el token expira en 1 hora
                        $this->tokenExpiration = now()->addHour();
                    }

                    Log::info("WhatsApp Service login successful", [
                        "token_length" => strlen($this->token),
                        "expires_at" => $this->tokenExpiration?->toISOString(),
                    ]);
                    return true;
                }
            }

            Log::error("WhatsApp Service login failed", [
                "status" => $response->status(),
                "response" => $response->json(),
                "response_body" => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error("WhatsApp Service login error", [
                "error" => $e->getMessage(),
                "error_class" => get_class($e),
                "trace" => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Verificar si el token es válido y no ha expirado
     */
    private function isTokenValid(): bool
    {
        if (!$this->token) {
            return false;
        }

        if ($this->tokenExpiration && $this->tokenExpiration->isPast()) {
            Log::info("WhatsApp Service token expired, need to re-login");
            return false;
        }

        return true;
    }

    /**
     * Obtener token válido (realizar login si es necesario)
     */
    private function getValidToken(): ?string
    {
        if ($this->isTokenValid()) {
            return $this->token;
        }

        // Intentar login para obtener nuevo token
        if ($this->login()) {
            return $this->token;
        }

        return null;
    }

    /**
     * Enviar mensaje de texto simple
     */
    public function sendTextMessage(string $to, string $message): array
    {
        // Usar endpoint de send-message-accept para mensajes personalizados
        $url = "{$this->baseUrl}/api/send-message-accept";

        $payload = [
            "telefono" => $this->formatPhoneNumber($to),
            "comentario" => $message,
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

        // Mapear templateOption a valores válidos del whatsapp-service
        $validOptions = [
            "confirmation" => "cita_gratis",
            "reminder" => "recordatorio_cita",
            "cancellation" => "recordatorio_cita", // usar recordatorio como base
        ];

        $payload = [
            "phone" => $this->formatPhoneNumber($to),
            "templateOption" => $validOptions[$templateOption] ?? "cita_gratis",
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
            "telefono" => $this->formatPhoneNumber($to),
            "comentario" => $message,
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
            "telefono" => $this->formatPhoneNumber($to),
            "comentario" => $message,
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
            "imageData" =>
                "data:image/" .
                strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) .
                ";base64," .
                $imageData,
            "caption" => $caption,
        ];

        return $this->makeRequest($url, $payload, "POST");
    }

    /**
     * Obtener estado de conexión del servicio WhatsApp
     */
    public function getConnectionStatus(): array
    {
        // Usar qr-status como alternativa ya que /api/status tiene un bug
        $url = "{$this->baseUrl}/api/qr-status";
        $result = $this->makeRequest($url, [], "GET");

        // Adaptar respuesta del qr-status para compatibilidad con getConnectionStatus
        if ($result["success"] && isset($result["data"]["isConnected"])) {
            $result["connected"] = $result["data"]["isConnected"];
            $result["data"]["connected"] = $result["data"]["isConnected"];

            // Agregar información adicional del estado de conexión
            if (isset($result["data"]["connectionState"])) {
                $connectionState = $result["data"]["connectionState"];
                $result["data"]["status"] =
                    $connectionState["status"] ?? "unknown";
                $result["data"]["socket_status"] =
                    $connectionState["socketStatus"] ?? "unknown";
                $result["data"]["reconnect_attempts"] =
                    $connectionState["reconnectAttempts"] ?? 0;
            }
        }

        return $result;
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
            Log::warning("WhatsApp Service: Error verificando conexión", [
                "error" => $e->getMessage(),
            ]);
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

        // Si empieza con + lo removemos
        if (substr($phone, 0, 1) === "+") {
            $phone = substr($phone, 1);
        }

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
            // Obtener token válido antes de hacer la petición
            $token = $this->getValidToken();

            if (!$token) {
                Log::error("WhatsApp Service: No se pudo obtener token", [
                    "url" => $url,
                    "method" => $method,
                ]);
                return [
                    "success" => false,
                    "error" =>
                        "No se pudo obtener token de autenticación para WhatsApp Service",
                ];
            }

            Log::info("WhatsApp Service: Realizando petición", [
                "url" => $url,
                "method" => $method,
                "has_token" => !empty($token),
                "payload" => array_merge($payload, ["token_hidden" => "***"]),
            ]);

            $httpClient = Http::withHeaders([
                "Authorization" => "Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json",
            ])->timeout($this->timeout);

            if ($method === "GET") {
                $response = $httpClient->get(
                    $url,
                    empty($payload) ? [] : ["query" => $payload],
                );
            } else {
                $response = $httpClient->post($url, $payload);
            }

            $responseData = $response->json();

            Log::info("WhatsApp Service: Respuesta recibida", [
                "url" => $url,
                "method" => $method,
                "status_code" => $response->status(),
                "response_size" => strlen($response->body()),
                "response_data" => $responseData,
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp message sent successfully", [
                    "url" => $url,
                    "method" => $method,
                    "phone" =>
                        $payload["phone"] ?? ($payload["telefono"] ?? null),
                    "response" => $responseData,
                ]);

                return [
                    "success" => $responseData["success"] ?? true,
                    "data" => $responseData,
                    "message_id" =>
                        $responseData["messageId"] ??
                        ($responseData["data"]["messageId"] ?? null),
                    "status" => "sent",
                ];
            }

            Log::error("WhatsApp Service API error", [
                "url" => $url,
                "method" => $method,
                "status" => $response->status(),
                "response" => $responseData,
                "response_body" => $response->body(),
                "payload" => $payload,
            ]);

            return [
                "success" => false,
                "error" =>
                    $responseData["message"] ??
                    ($responseData["error"] ??
                        "Error desconocido del servicio WhatsApp"),
                "error_code" => $response->status(),
                "error_details" => $responseData,
            ];
        } catch (Exception $e) {
            Log::error("WhatsApp service connection error", [
                "url" => $url,
                "method" => $method,
                "error" => $e->getMessage(),
                "error_class" => get_class($e),
                "payload" => $payload,
                "trace" => $e->getTraceAsString(),
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
     * Forzar nuevo login (útil para renovar token manualmente)
     */
    public function refreshToken(): array
    {
        $this->token = null;
        $this->tokenExpiration = null;

        if ($this->login()) {
            return [
                "success" => true,
                "message" => "Token renovado exitosamente",
                "expires_at" => $this->tokenExpiration
                    ? $this->tokenExpiration->toISOString()
                    : null,
            ];
        }

        return [
            "success" => false,
            "error" => "No se pudo renovar el token",
        ];
    }

    /**
     * Obtener información del token actual
     */
    public function getTokenInfo(): array
    {
        return [
            "has_token" => !is_null($this->token),
            "expires_at" => $this->tokenExpiration
                ? $this->tokenExpiration->toISOString()
                : null,
            "is_valid" => $this->isTokenValid(),
            "username" => $this->username,
        ];
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
                    "connected" => $status["connected"] ?? false,
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
        // Mapear templates a los valores válidos del whatsapp-service
        $validTemplates = [
            "appointment_confirmation" => "cita_gratis",
            "appointment_reminder" => "recordatorio_cita",
            "appointment_cancellation" => "recordatorio_cita",
            "cita_gratis" => "cita_gratis",
            "cita_pagada" => "cita_pagada",
            "recordatorio_cita" => "recordatorio_cita",
            "confirmacion_asistencia" => "confirmacion_asistencia",
        ];

        if (isset($validTemplates[$templateName]) && count($parameters) >= 3) {
            // Es un template de cita válido con parámetros suficientes
            return $this->sendAppointmentMessage(
                $to,
                $parameters[0] ?? "",
                $parameters[1] ?? "",
                $parameters[2] ?? "",
                array_search($validTemplates[$templateName], $validTemplates) ?:
                "confirmation",
            );
        } else {
            // Para templates no reconocidos o sin parámetros, enviar como mensaje de texto
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
