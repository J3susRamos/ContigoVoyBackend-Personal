<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppBusinessService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WhatsAppController extends Controller
{
    private $whatsappService;

    public function __construct(WhatsAppBusinessService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Enviar mensaje de confirmación de cita
     */
    public function sendAppointmentConfirmation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:9',
            'name' => 'required|string|max:100',
            'date' => 'required|date',
            'time' => 'required|string',
            'service' => 'string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Formatear fecha para mostrar mejor
        $formattedDate = \Carbon\Carbon::parse($data['date'])->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');

        $message = "¡Hola {$data['name']}! 👋\n\n" .
            "✅ Tu cita ha sido confirmada:\n" .
            "📅 Fecha: {$formattedDate}\n" .
            "🕐 Hora: {$data['time']}\n";

        if (isset($data['service'])) {
            $message .= "🔹 Servicio: {$data['service']}\n";
        }

        $message .= "\n¡Te esperamos! Si tienes alguna consulta, no dudes en contactarnos.";

        $result = $this->whatsappService->sendTextMessage($data['phone'], $message);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Mensaje de confirmación enviado',
                'whatsapp_message_id' => $result['message_id']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al enviar mensaje de WhatsApp',
            'error' => $result['error']
        ], 500);
    }

    /**
     * Enviar recordatorio de cita
     */
    public function sendAppointmentReminder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:9',
            'name' => 'required|string|max:100',
            'date' => 'required|date',
            'time' => 'required|string',
            'hours_before' => 'integer|min:1|max:72'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $hoursBefore = $data['hours_before'] ?? 24;

        $formattedDate = \Carbon\Carbon::parse($data['date'])->locale('es')->isoFormat('dddd, D [de] MMMM');

        $message = "🔔 Recordatorio de cita\n\n" .
            "Hola {$data['name']}, te recordamos que tienes una cita:\n\n" .
            "📅 {$formattedDate}\n" .
            "🕐 {$data['time']}\n\n" .
            "Si necesitas reprogramar o cancelar, contáctanos con anticipación.\n\n" .
            "¡Te esperamos!";

        $result = $this->whatsappService->sendTextMessage($data['phone'], $message);

        return response()->json($result);
    }

    /**
     * Enviar mensaje con opciones interactivas
     */
    public function sendInteractiveMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:9',
            'message' => 'required|string|max:1024',
            'buttons' => 'required|array|min:1|max:3',
            'buttons.*.title' => 'required|string|max:20',
            'buttons.*.id' => 'string|max:256'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $result = $this->whatsappService->sendButtonMessage(
            $data['phone'],
            $data['message'],
            $data['buttons']
        );

        return response()->json($result);
    }

    /**
     * Webhook para recibir mensajes de WhatsApp
     */
    public function webhook(Request $request)
    {
        // Verificación del webhook (GET request)
        if ($request->isMethod('get')) {
            $mode = $request->query('hub_mode');
            $token = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            $verifyToken = config('services.whatsapp.verify_token');

            if ($mode === 'subscribe' && $token === $verifyToken) {
                Log::info('WhatsApp webhook verified successfully');
                return response($challenge, 200);
            }

            Log::warning('WhatsApp webhook verification failed', [
                'mode' => $mode,
                'token' => $token
            ]);

            return response('Forbidden', 403);
        }

        // Procesar mensajes entrantes (POST request)
        if ($request->isMethod('post')) {
            $body = $request->all();

            Log::info('WhatsApp webhook received', ['body' => $body]);

            if (isset($body['entry'])) {
                foreach ($body['entry'] as $entry) {
                    if (isset($entry['changes'])) {
                        foreach ($entry['changes'] as $change) {
                            if ($change['field'] === 'messages') {
                                $this->processIncomingMessage($change['value']);
                            }
                        }
                    }
                }
            }

            return response('OK', 200);
        }

        return response('Method not allowed', 405);
    }

    /**
     * Procesar mensajes entrantes
     */
    private function processIncomingMessage(array $value)
    {
        if (!isset($value['messages'])) {
            return;
        }

        foreach ($value['messages'] as $message) {
            $from = $message['from'];
            $messageId = $message['id'];
            $timestamp = $message['timestamp'];

            // Procesar según el tipo de mensaje
            if ($message['type'] === 'text') {
                $text = $message['text']['body'];
                Log::info('Text message received', [
                    'from' => $from,
                    'text' => $text,
                    'message_id' => $messageId
                ]);

                // Aquí puedes agregar lógica para responder automáticamente
                $this->handleTextMessage($from, $text);
            } elseif ($message['type'] === 'interactive') {
                if (isset($message['interactive']['button_reply'])) {
                    $buttonId = $message['interactive']['button_reply']['id'];
                    Log::info('Button click received', [
                        'from' => $from,
                        'button_id' => $buttonId
                    ]);

                    $this->handleButtonClick($from, $buttonId);
                }
            }
        }
    }

    /**
     * Manejar mensajes de texto entrantes
     */
    private function handleTextMessage(string $from, string $text)
    {
        $text = strtolower(trim($text));

        // Respuestas automáticas simples
        switch ($text) {
            case 'hola':
            case 'hello':
                $response = "¡Hola! 👋 Gracias por contactarnos. ¿En qué podemos ayudarte?";
                $this->whatsappService->sendTextMessage($from, $response);
                break;

            case 'horarios':
            case 'horario':
                $response = "📅 Nuestros horarios de atención:\n\n" .
                    "Lunes a Viernes: 8:00 AM - 6:00 PM\n" .
                    "Sábados: 8:00 AM - 2:00 PM\n" .
                    "Domingos: Cerrado";
                $this->whatsappService->sendTextMessage($from, $response);
                break;

            case 'info':
            case 'información':
                $buttons = [
                    ['id' => 'horarios', 'title' => 'Horarios'],
                    ['id' => 'servicios', 'title' => 'Servicios'],
                    ['id' => 'contacto', 'title' => 'Contacto']
                ];

                $this->whatsappService->sendButtonMessage(
                    $from,
                    "¿Qué información necesitas?",
                    $buttons
                );
                break;
        }
    }

    /**
     * Manejar clicks de botones
     */
    private function handleButtonClick(string $from, string $buttonId)
    {
        switch ($buttonId) {
            case 'horarios':
                $response = "📅 Nuestros horarios:\nLun-Vie: 8AM-6PM\nSáb: 8AM-2PM\nDom: Cerrado";
                break;

            case 'servicios':
                $response = "🔹 Nuestros servicios:\n- Consultas generales\n- Especialidades\n- Emergencias\n- Telemedicina";
                break;

            case 'contacto':
                $response = "📞 Contacto:\nTeléfono: (01) 123-4567\nEmail: info@miclinica.com\nDirección: Av. Principal 123, Lima";
                break;

            default:
                $response = "Gracias por tu interés. Un agente se comunicará contigo pronto.";
        }

        $this->whatsappService->sendTextMessage($from, $response);
    }

    /**
     * Obtener estado del servicio
     */
    public function status(): JsonResponse
    {
        $profile = $this->whatsappService->getBusinessProfile();

        return response()->json([
            'service' => 'WhatsApp Business API',
            'status' => $profile['success'] ? 'connected' : 'error',
            'timestamp' => now()->toISOString(),
            'profile' => $profile['data'] ?? null
        ]);
    }
}
