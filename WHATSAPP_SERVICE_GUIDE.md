# 🚀 WhatsApp Service - Guía Completa

## 📋 Descripción General

El **WhatsApp Service** es un servicio basado en Baileys que proporciona una interfaz completa para enviar mensajes de WhatsApp. Incluye **autenticación automática** mediante login con credenciales, gestión inteligente de tokens y funcionalidades avanzadas de mensajería.

## 🔐 Sistema de Autenticación

### ✨ Login Automático
El servicio ahora incluye un sistema de **login automático** que:
- 🔑 Obtiene tokens automáticamente usando credenciales
- ⏰ Gestiona la expiración y renovación de tokens
- 🔄 Reintenta el login cuando es necesario
- 📝 Registra todas las operaciones de autenticación

### 🔧 Configuración de Credenciales
```env
# Variables de entorno requeridas
WHATSAPP_SERVICE_URL=https://whatsapi.centropsicologicocontigovoy.com
WHATSAPP_SERVICE_USERNAME=admin
WHATSAPP_SERVICE_PASSWORD=admin123
WHATSAPP_SERVICE_TIMEOUT=30
```

### 🚫 Variables Obsoletas (ya no se usan)
```env
# ❌ YA NO SE NECESITA
# WHATSAPP_SERVICE_TOKEN=manual_token
```

## 🛠️ Configuración en Laravel

### config/services.php
```php
"whatsapp_service" => [
    "base_url" => env("WHATSAPP_SERVICE_URL", "http://localhost:5111"),
    "username" => env("WHATSAPP_SERVICE_USERNAME", "admin"),
    "password" => env("WHATSAPP_SERVICE_PASSWORD", "admin123"),
    "timeout" => env("WHATSAPP_SERVICE_TIMEOUT", 30),
],
```

## 🎯 Funcionalidades Principales

### 📱 Métodos de Mensajería
```php
$whatsappService = app(\App\Services\WhatsAppService::class);

// Mensaje de texto simple
$whatsappService->sendTextMessage('51987654321', '¡Hola! Este es un mensaje de prueba');

// Mensaje de confirmación de cita
$whatsappService->sendConfirmationMessage(
    '51987654321',
    'Dr. García',
    '15/12/2024',
    '10:30'
);

// Mensaje de recordatorio
$whatsappService->sendReminderMessage(
    '51987654321',
    'Dra. López',
    '20/12/2024',
    '15:00'
);

// Mensaje de cancelación
$whatsappService->sendCancellationMessage(
    '51987654321',
    'Dr. Martínez',
    '18/12/2024',
    '09:00'
);

// Mensaje con imagen
$whatsappService->sendImageMessage(
    '51987654321',
    '/path/to/image.jpg',
    'Caption opcional'
);
```

### 🔍 Métodos de Estado y Gestión
```php
// Verificar estado de conexión
$status = $whatsappService->getConnectionStatus();

// Verificar si está conectado
$isConnected = $whatsappService->isConnected();

// Obtener código QR
$qr = $whatsappService->getQRCode();

// Estado del QR
$qrStatus = $whatsappService->getQRStatus();

// Forzar reconexión
$whatsappService->forceReconnect();

// Resetear autenticación
$whatsappService->resetAuth();
```

### 🔐 Nuevos Métodos de Autenticación
```php
// Información del token actual
$tokenInfo = $whatsappService->getTokenInfo();

// Renovar token manualmente
$result = $whatsappService->refreshToken();
```

### 🔄 Métodos de Compatibilidad
```php
// Template messages (mapeados automáticamente)
$whatsappService->sendTemplateMessage(
    '51987654321',
    'appointment_confirmation',
    ['Dr. Pérez', '25/12/2024', '11:00']
);

// Mensaje con botones (convertido a texto)
$whatsappService->sendButtonMessage(
    '51987654321',
    'Selecciona una opción:',
    [
        ['title' => 'Confirmar cita'],
        ['title' => 'Reprogramar'],
        ['title' => 'Cancelar']
    ]
);

// Mensaje con lista (convertido a texto)
$whatsappService->sendListMessage(
    '51987654321',
    'Elige un horario:',
    'Horarios disponibles',
    [
        [
            'title' => 'Mañana',
            'rows' => [
                ['title' => '09:00', 'description' => 'Disponible'],
                ['title' => '10:00', 'description' => 'Disponible']
            ]
        ]
    ]
);
```

## 🖥️ Comandos de Gestión

### 📋 Comando Principal
```bash
php artisan whatsapp:service
```

### 🔍 Opciones Disponibles
```bash
# Ver estado detallado del servicio
php artisan whatsapp:service --status

# Información del código QR
php artisan whatsapp:service --qr

# Enviar mensaje de prueba
php artisan whatsapp:service --test

# Forzar reconexión
php artisan whatsapp:service --reconnect

# 🆕 Información del token de autenticación
php artisan whatsapp:service --token

# 🆕 Renovar token manualmente
php artisan whatsapp:service --refresh-token
```

## 🔄 Flujo de Autenticación Automática

### 1. **Primer Uso**
```
┌─ Constructor ─┐
│ No hay token  │
└───────────────┘
        │
        ▼
┌─ Primera petición ─┐
│ getValidToken()    │
└────────────────────┘
        │
        ▼
┌─ Login automático ─┐
│ POST /api/auth/login │
│ username: admin      │
│ password: admin123   │
└──────────────────────┘
        │
        ▼
┌─ Token obtenido ─┐
│ Guarda en memoria │
│ Establece expiración │
└───────────────────┘
        │
        ▼
┌─ Petición exitosa ─┐
│ Con token válido    │
└─────────────────────┘
```

### 2. **Uso Posterior**
```
┌─ Nueva petición ─┐
│ getValidToken()  │
└──────────────────┘
        │
        ▼
┌─ ¿Token válido? ─┐
│ Verifica expiración │
└─────────────────────┘
        │
    ┌───▼───┐
    │  SÍ   │  NO
    ▼       ▼
┌─ Usar    ┐ ┌─ Login ─┐
│ token     │ │ nuevo   │
│ actual    │ │ token   │
└───────────┘ └─────────┘
```

### 3. **Renovación de Token**
- ⏰ **Automática**: Cuando el token expira
- 🔧 **Manual**: Usando `refreshToken()` o comando `--refresh-token`
- 🔄 **Por error**: Si una petición falla por token inválido

## 📊 Respuestas de la API

### ✅ Respuesta Exitosa
```php
[
    "success" => true,
    "data" => [...],
    "message_id" => "msg_123456",
    "status" => "sent"
]
```

### ❌ Respuesta de Error
```php
[
    "success" => false,
    "error" => "Descripción del error",
    "error_code" => 400,
    "error_details" => [...]
]
```

### 🔐 Error de Autenticación
```php
[
    "success" => false,
    "error" => "No se pudo obtener token de autenticación para WhatsApp Service"
]
```

## 🚨 Manejo de Errores

### 🔑 Errores de Login
- **Credenciales incorrectas**: Verifica username/password
- **Servicio no disponible**: Verifica la URL del servicio
- **Timeout de conexión**: Ajusta WHATSAPP_SERVICE_TIMEOUT

### 📱 Errores de Mensajería
- **Token expirado**: Se renueva automáticamente
- **Servicio desconectado**: Usa `--reconnect`
- **Número inválido**: Verifica formato del número

### 🔧 Comandos de Diagnóstico
```bash
# Verificar configuración
php artisan whatsapp:service

# Ver estado completo
php artisan whatsapp:service --status

# Verificar token
php artisan whatsapp:service --token

# Probar conectividad
php artisan whatsapp:service --test
```

## 📝 Logs y Monitoreo

### 🔍 Logs Importantes
```php
// Login exitoso
Log::info("WhatsApp Service login successful");

// Token expirado
Log::info("WhatsApp Service token expired, need to re-login");

// Error de login
Log::error("WhatsApp Service login failed", [...]);

// Mensaje enviado
Log::info("WhatsApp message sent successfully", [...]);

// Error de servicio
Log::error("WhatsApp Service API error", [...]);
```

### 📊 Información de Token
```php
$tokenInfo = $whatsappService->getTokenInfo();
/*
[
    "has_token" => true,
    "expires_at" => "2024-12-15T15:30:00.000Z",
    "is_valid" => true,
    "username" => "admin"
]
*/
```

## 🎯 Ejemplos de Uso

### 📧 En PrePacienteController
```php
try {
    $whatsappService = app(\App\Services\WhatsAppService::class);
    
    $mensaje = "¡Hola {$prePaciente->nombre}! 👋\n\n" .
               "✅ Tu primera cita GRATUITA ha sido confirmada:\n\n" .
               "📅 Fecha: {$fecha}\n" .
               "🕐 Hora: {$hora}\n" .
               "👨‍⚕️ Psicólogo: {$nombrePsicologo}";

    $result = $whatsappService->sendTextMessage(
        $prePaciente->celular,
        $mensaje
    );

    if ($result['success']) {
        Log::info('WhatsApp sent successfully', [
            'patient' => $prePaciente->nombre,
            'message_id' => $result['message_id']
        ]);
    }
} catch (\Exception $e) {
    Log::error('WhatsApp service error: ' . $e->getMessage());
}
```

### 🔄 Renovación Manual de Token
```php
$whatsappService = app(\App\Services\WhatsAppService::class);
$result = $whatsappService->refreshToken();

if ($result['success']) {
    echo "Token renovado exitosamente";
    echo "Expira el: " . $result['expires_at'];
}
```

## 🛡️ Seguridad

### 🔒 Mejores Prácticas
- ✅ **Credenciales en .env**: Nunca hardcodear en código
- ✅ **Logs seguros**: No registrar passwords en logs
- ✅ **Token temporal**: Se renueva automáticamente
- ✅ **Timeout configurable**: Evita bloqueos largos
- ✅ **Manejo de errores**: Fallos controlados

### 🚫 Qué NO hacer
- ❌ No almacenar credenciales en código
- ❌ No compartir tokens entre servicios
- ❌ No ignorar errores de autenticación
- ❌ No usar timeouts muy largos

## 🆕 Nuevas Funcionalidades

### 🔐 Sistema de Login Automático
- **Login transparente**: Se hace automáticamente
- **Gestión de expiración**: Renovación inteligente
- **Comandos de gestión**: Control manual del token
- **Logs detallados**: Monitoreo completo

### 🎛️ Comandos Mejorados
- `--token`: Información del token actual
- `--refresh-token`: Renovación manual
- Estado detallado del sistema de autenticación

### 🔄 Compatibilidad Mantenida
- Todos los métodos anteriores funcionan igual
- Sin cambios en la interfaz pública
- Transición transparente desde token manual

## 🚀 Migración desde Token Manual

### Antes (Token Manual)
```env
WHATSAPP_SERVICE_TOKEN=tu_token_manual_aqui
```

### Ahora (Login Automático)
```env
WHATSAPP_SERVICE_USERNAME=admin
WHATSAPP_SERVICE_PASSWORD=admin123
```

### Sin Cambios en el Código
```php
// El código sigue funcionando igual
$whatsappService = app(\App\Services\WhatsAppService::class);
$result = $whatsappService->sendTextMessage('51987654321', 'Mensaje');
```

---

## 📞 Soporte

- 🐛 **Errores**: Revisar logs de Laravel
- 🔧 **Configuración**: Verificar variables de entorno  
- 📱 **Conectividad**: Usar comandos de diagnóstico
- 🔐 **Autenticación**: Verificar credenciales y token

**¡El WhatsApp Service ahora es más robusto, seguro y fácil de gestionar!** 🎉