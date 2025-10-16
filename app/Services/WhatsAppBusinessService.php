<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppBusinessService
{
    private $token;
    private $phoneNumberId;
    private $apiVersion;
    private $baseUrl;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->apiVersion = config('services.whatsapp.api_version');
        $this->baseUrl = config('services.whatsapp.base_url');
    }

    /**
     * Enviar mensaje de texto simple
     */
    public function sendTextMessage(string $to, string $message): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];

        return $this->makeRequest($url, $payload);
    }

    /**
     * Enviar mensaje con template (para mensajes masivos)
     */
    public function sendTemplateMessage(string $to, string $templateName, array $parameters = []): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => 'es' // Español
                ]
            ]
        ];

        // Agregar parámetros si los hay
        if (!empty($parameters)) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => array_map(function ($param) {
                        return ['type' => 'text', 'text' => $param];
                    }, $parameters)
                ]
            ];
        }

        return $this->makeRequest($url, $payload);
    }

    /**
     * Enviar mensaje interactivo con botones
     */
    public function sendButtonMessage(string $to, string $body, array $buttons): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        $interactiveButtons = array_map(function ($button, $index) {
            return [
                'type' => 'reply',
                'reply' => [
                    'id' => $button['id'] ?? "btn_{$index}",
                    'title' => substr($button['title'], 0, 20) // Máximo 20 caracteres
                ]
            ];
        }, $buttons, array_keys($buttons));

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => $body
                ],
                'action' => [
                    'buttons' => array_slice($interactiveButtons, 0, 3) // Máximo 3 botones
                ]
            ]
        ];

        return $this->makeRequest($url, $payload);
    }

    /**
     * Enviar mensaje con lista desplegable
     */
    public function sendListMessage(string $to, string $body, string $buttonText, array $sections): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => [
                    'text' => $body
                ],
                'action' => [
                    'button' => $buttonText,
                    'sections' => $sections
                ]
            ]
        ];

        return $this->makeRequest($url, $payload);
    }

    /**
     * Formatear número de teléfono para Perú
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remover caracteres no numéricos
        $phone = preg_replace('/[^\d]/', '', $phone);

        // Si tiene 9 dígitos, agregar código de país de Perú (51)
        if (strlen($phone) === 9) {
            $phone = '51' . $phone;
        }

        // Si no tiene código de país y tiene más de 9 dígitos, asumir que ya lo tiene
        return $phone;
    }

    /**
     * Realizar petición HTTP a WhatsApp API
     */
    private function makeRequest(string $url, array $payload): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($url, $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'to' => $payload['to'],
                    'type' => $payload['type'],
                    'message_id' => $responseData['messages'][0]['id'] ?? null
                ]);

                return [
                    'success' => true,
                    'message_id' => $responseData['messages'][0]['id'] ?? null,
                    'status' => $responseData['messages'][0]['message_status'] ?? 'sent',
                    'data' => $responseData
                ];
            }

            Log::error('WhatsApp API error', [
                'status' => $response->status(),
                'response' => $responseData,
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'error' => $responseData['error']['message'] ?? 'Error desconocido',
                'error_code' => $responseData['error']['code'] ?? null,
                'error_details' => $responseData['error'] ?? null
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp service exception', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'error' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener información del perfil business
     */
    public function getBusinessProfile(): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->get($url, ['fields' => 'display_phone_number,verified_name,quality_rating']);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Error al obtener perfil'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
