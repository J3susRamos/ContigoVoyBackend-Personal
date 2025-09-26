<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use App\Services\WhatsAppService;
use App\Models\User;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Psicologo;
use App\Models\NotificationLog;

class WhatsAppServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $whatsappService;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar variables de prueba
        Config::set('services.whatsapp_service', [
            'base_url' => 'http://localhost:5111',
            'token' => 'test_token_jwt',
            'timeout' => 30,
        ]);

        // Crear usuario de prueba
        $this->user = User::factory()->create([
            'role' => 'ADMIN'
        ]);

        $this->whatsappService = app(WhatsAppService::class);
    }

    /** @test */
    public function can_send_text_message()
    {
        // Mock del HTTP client
        Http::fake([
            'http://localhost:5111/api/send-message' => Http::response([
                'success' => true,
                'messageId' => 'msg_123456',
                'status' => 'sent'
            ], 200)
        ]);

        $result = $this->whatsappService->sendTextMessage(
            '51987654321',
            'Mensaje de prueba'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('msg_123456', $result['message_id']);
        $this->assertEquals('sent', $result['status']);

        // Verificar que se hizo la llamada HTTP correcta
        Http::assertSent(function ($request) {
            return $request->url() === 'http://localhost:5111/api/send-message' &&
                   $request['phone'] === '51987654321' &&
                   $request['customMessage'] === 'Mensaje de prueba' &&
                   $request->header('Authorization')[0] === 'Bearer test_token_jwt';
        });
    }

    /** @test */
    public function can_send_appointment_confirmation_message()
    {
        Http::fake([
            'http://localhost:5111/api/send-message' => Http::response([
                'success' => true,
                'messageId' => 'msg_789012',
                'status' => 'sent'
            ], 200)
        ]);

        $result = $this->whatsappService->sendConfirmationMessage(
            '51987654321',
            'Dr. Juan Pérez',
            '2024-01-20',
            '14:30'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('msg_789012', $result['message_id']);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://localhost:5111/api/send-message' &&
                   $request['templateOption'] === 'confirmation' &&
                   $request['psicologo'] === 'Dr. Juan Pérez' &&
                   $request['fecha'] === '2024-01-20' &&
                   $request['hora'] === '14:30';
        });
    }

    /** @test */
    public function can_get_connection_status()
    {
        Http::fake([
            'http://localhost:5111/api/status' => Http::response([
                'success' => true,
                'connected' => true,
                'timestamp' => now()->toISOString()
            ], 200)
        ]);

        $result = $this->whatsappService->getConnectionStatus();

        $this->assertTrue($result['success']);
        $this->assertTrue($result['data']['connected']);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://localhost:5111/api/status' &&
                   $request->method() === 'GET';
        });
    }

    /** @test */
    public function can_check_if_connected()
    {
        Http::fake([
            'http://localhost:5111/api/status' => Http::response([
                'success' => true,
                'connected' => true
            ], 200)
        ]);

        $isConnected = $this->whatsappService->isConnected();

        $this->assertTrue($isConnected);
    }

    /** @test */
    public function returns_false_when_service_unavailable()
    {
        Http::fake([
            'http://localhost:5111/api/status' => Http::response([], 500)
        ]);

        $isConnected = $this->whatsappService->isConnected();

        $this->assertFalse($isConnected);
    }

    /** @test */
    public function formats_phone_number_correctly()
    {
        Http::fake([
            'http://localhost:5111/api/send-message' => Http::response([
                'success' => true,
                'messageId' => 'msg_123'
            ], 200)
        ]);

        // Probar con número de 9 dígitos (se debe agregar código de país 51)
        $this->whatsappService->sendTextMessage('987654321', 'Test');

        Http::assertSent(function ($request) {
            return $request['phone'] === '51987654321';
        });

        Http::fake([
            'http://localhost:5111/api/send-message' => Http::response([
                'success' => true,
                'messageId' => 'msg_124'
            ], 200)
        ]);

        // Probar con número que ya tiene código de país
        $this->whatsappService->sendTextMessage('51987654321', 'Test');

        Http::assertSent(function ($request) {
            return $request['phone'] === '51987654321';
        });
    }

    /** @test */
    public function handles_api_errors_gracefully()
    {
        Http::fake([
            'http://localhost:5111/api/send-message' => Http::response([
                'success' => false,
                'message' => 'WhatsApp not connected'
            ], 424)
        ]);

        $result = $this->whatsappService->sendTextMessage(
            '51987654321',
            'Test message'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContains('WhatsApp not connected', $result['error']);
        $this->assertEquals(424, $result['error_code']);
    }

    /** @test */
    public function handles_connection_timeout()
    {
        Http::fake([
            'http://localhost:5111/api/send-message' => function () {
                throw new \Exception('Connection timeout');
            }
        ]);

        $result = $this->whatsappService->sendTextMessage(
            '51987654321',
            'Test message'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContains('Connection timeout', $result['error']);
    }

    /** @test */
    public function can_send_appointment_confirmation_via_api()
    {
        Http::fake([
            'http://localhost:5111/api/send-message' => Http::response([
                'success' => true,
                'messageId' => 'msg_api_test'
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
                        ->postJson('/api/whatsapp/send-confirmation', [
                            'phone' => '987654321',
                            'name' => 'Juan Pérez',
                            'date' => '2024-01-20',
                            'time' => '14:30',
                            'service' => 'Consulta Psicológica'
                        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Mensaje de confirmación enviado'
                 ]);
    }

    /** @test */
    public function api_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
                        ->postJson('/api/whatsapp/send-confirmation', [
                            'phone' => '987654321',
                            // Falta name, date, time
                        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'success',
                     'errors' => [
                         'name',
                         'date',
                         'time'
                     ]
                 ]);
    }

    /** @test */
    public function can_get_service_status_via_api()
    {
        Http::fake([
            'http://localhost:5111/api/status' => Http::response([
                'success' => true,
                'connected' => true
            ], 200),
            'http://localhost:5111/api/qr-status' => Http::response([
                'success' => true,
                'data' => [
                    'hasActiveQR' => false,
                    'connectionState' => ['status' => 'connected']
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
                        ->getJson('/api/whatsapp/status');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'service',
                     'status',
                     'connected',
                     'timestamp',
                     'connection_info',
                     'qr_info'
                 ])
                 ->assertJson([
                     'service' => 'WhatsApp Service (Baileys)',
                     'status' => 'connected',
                     'connected' => true
                 ]);
    }

    /** @test */
    public function can_request_new_qr_code()
    {
        Http::fake([
            'http://localhost:5111/api/qr-request' => Http::response([
                'success' => true,
                'message' => 'New QR code generated'
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
                        ->postJson('/api/whatsapp/qr-request');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://localhost:5111/api/qr-request' &&
                   $request->method() === 'POST';
        });
    }

    /** @test */
    public function can_force_reconnect()
    {
        Http::fake([
            'http://localhost:5111/api/force-reconnect' => Http::response([
                'success' => true,
                'message' => 'Reconnection initiated'
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
                        ->postJson('/api/whatsapp/force-reconnect');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }

    /** @test */
    public function handles_service_unavailable()
    {
        // Simular servicio no disponible
        Http::fake([
            '*' => Http::response([], 500)
        ]);

        $response = $this->actingAs($this->user)
                        ->getJson('/api/whatsapp/status');

        $response->assertStatus(200); // El controlador maneja el error internamente

        $data = $response->json();
        $this->assertEquals('disconnected', $data['status']);
        $this->assertFalse($data['connected']);
    }

    /** @test */
    public function sends_interactive_message_as_text_with_options()
    {
        Http::fake([
            'http://localhost:5111/api/send-message' => Http::response([
                'success' => true,
                'messageId' => 'msg_interactive'
            ], 200)
        ]);

        $result = $this->whatsappService->sendButtonMessage(
            '51987654321',
            '¿Qué información necesitas?',
            [
                ['title' => 'Horarios', 'id' => 'horarios'],
                ['title' => 'Servicios', 'id' => 'servicios']
            ]
        );

        $this->assertTrue($result['success']);

        // Verificar que se envió como mensaje de texto con opciones
        Http::assertSent(function ($request) {
            $message = $request['customMessage'];
            return strpos($message, '¿Qué información necesitas?') !== false &&
                   strpos($message, '1. Horarios') !== false &&
                   strpos($message, '2. Servicios') !== false;
        });
    }

    /** @test */
    public function supports_template_compatibility()
    {
        Http::fake([
            'http://localhost:5111/api/send-message' => Http::response([
                'success' => true,
                'messageId' => 'msg_template'
            ], 200)
        ]);

        // Probar compatibilidad con templates del Business API
        $result = $this->whatsappService->sendTemplateMessage(
            '51987654321',
            'appointment_confirmation',
            ['Dr. Juan Pérez', '2024-01-20', '14:30']
        );

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            return $request['templateOption'] === 'confirmation' &&
                   $request['psicologo'] === 'Dr. Juan Pérez' &&
                   $request['fecha'] === '2024-01-20' &&
                   $request['hora'] === '14:30';
        });
    }

    /** @test */
    public function logs_messages_correctly()
    {
        Http::fake([
            'http://localhost:5111/api/send-message' => Http::response([
                'success' => true,
                'messageId' => 'msg_logged'
            ], 200)
        ]);

        // Crear una cita y notificación para probar el logging
        $paciente = Paciente::factory()->create(['celular' => '987654321']);
        $cita = Cita::factory()->create(['id_Paciente' => $paciente->id]);

        $notification = NotificationLog::create([
            'idCita' => $cita->idCita,
            'tipo_notificacion' => 'recordatorio_24_horas',
            'telefono' => '987654321',
            'mensaje' => 'Recordatorio de cita',
            'estado' => 'pendiente',
            'fecha_programada' => now()
        ]);

        $result = $this->whatsappService->sendTextMessage(
            $notification->telefono,
            $notification->mensaje
        );

        $this->assertTrue($result['success']);

        // Verificar que el resultado incluye información para logging
        $this->assertArrayHasKey('message_id', $result);
        $this->assertArrayHasKey('status', $result);
    }

    /** @test */
    public function requires_authentication_for_protected_endpoints()
    {
        $response = $this->getJson('/api/whatsapp/status');

        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function webhook_verification_works()
    {
        $response = $this->getJson('/api/whatsapp/webhook', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => config('services.whatsapp_service.token'),
            'hub_challenge' => 'test_challenge'
        ]);

        $response->assertStatus(200)
                 ->assertSeeText('test_challenge');
    }

    /** @test */
    public function webhook_verification_fails_with_wrong_token()
    {
        $response = $this->getJson('/api/whatsapp/webhook', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'wrong_token',
            'hub_challenge' => 'test_challenge'
        ]);

        $response->assertStatus(403)
                 ->assertSeeText('Forbidden');
    }

    protected function tearDown(): void
    {
        Http::assertNothingOutstanding();
        parent::tearDown();
    }
}
