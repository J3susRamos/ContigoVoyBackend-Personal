<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AutomatedNotificationService
{
    private $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Programa todas las notificaciones para una cita
     */
    public function programarNotificacionesPorCita($idCita)
    {
        $cita = Cita::with([
            "paciente",
            "prepaciente",
            "psicologo.users",
        ])->find($idCita);

        if (!$cita) {
            Log::error("Cita no encontrada: {$idCita}");
            return false;
        }

        $fechaHoraCita = Carbon::parse(
            $cita->fecha_cita . " " . $cita->hora_cita,
        );

        // Obtener teléfono del paciente o prepaciente
        $telefono = $cita->paciente
            ? $cita->paciente->celular
            : ($cita->prepaciente
                ? $cita->prepaciente->celular
                : null);

        if (!$telefono) {
            Log::error("No se encontró teléfono para la cita: {$idCita}");
            return false;
        }

        // Programar diferentes tipos de notificaciones
        $this->programarNotificacion(
            $cita,
            "recordatorio_24_horas",
            $fechaHoraCita->subHours(24),
            $telefono,
        );
        $this->programarNotificacion(
            $cita,
            "recordatorio_pago_3_horas",
            $fechaHoraCita->subHours(3),
            $telefono,
        );
        $this->programarNotificacion(
            $cita,
            "recordatorio_1_hora",
            $fechaHoraCita->subHour(1),
            $telefono,
        );
        $this->programarNotificacion(
            $cita,
            "recordatorio_30_minutos",
            $fechaHoraCita->subMinutes(30),
            $telefono,
        );

        return true;
    }

    /**
     * Programa una notificación específica
     */
    private function programarNotificacion(
        $cita,
        $tipoNotificacion,
        $fechaProgramada,
        $telefono,
    ) {
        // Verificar si ya existe esta notificación
        $exists = NotificationLog::where("idCita", $cita->idCita)
            ->where("tipo_notificacion", $tipoNotificacion)
            ->exists();

        if ($exists) {
            return; // Ya está programada
        }

        $mensaje = $this->generarMensaje($cita, $tipoNotificacion);

        NotificationLog::create([
            "idCita" => $cita->idCita,
            "tipo_notificacion" => $tipoNotificacion,
            "telefono" => $telefono,
            "mensaje" => $mensaje,
            "estado" => "pendiente",
            "fecha_programada" => $fechaProgramada,
        ]);
    }

    /**
     * Procesa todas las notificaciones pendientes que ya es hora de enviar
     */
    public function procesarNotificacionesPendientes()
    {
        $ahora = Carbon::now();

        $notificacionesPendientes = NotificationLog::with(
            "cita.paciente",
            "cita.prepaciente",
            "cita.psicologo.users",
        )
            ->where("estado", "pendiente")
            ->where("fecha_programada", "<=", $ahora)
            ->get();

        foreach ($notificacionesPendientes as $notificacion) {
            $this->enviarNotificacion($notificacion);
        }

        Log::info(
            "Procesadas " .
                count($notificacionesPendientes) .
                " notificaciones pendientes",
        );
    }

    /**
     * Envía una notificación específica
     */
    private function enviarNotificacion(NotificationLog $notificacion)
    {
        try {
            // Verificar que la cita sigue siendo válida
            if (!$this->validarCitaParaNotificacion($notificacion)) {
                $notificacion->update([
                    "estado" => "error",
                    "error_mensaje" => "Cita cancelada o no válida",
                ]);
                return;
            }

            $resultado = $this->whatsappService->sendTextMessage(
                $notificacion->telefono,
                $notificacion->mensaje,
            );

            if ($resultado["success"]) {
                $notificacion->update([
                    "estado" => "enviado",
                    "whatsapp_message_id" => $resultado["message_id"] ?? null,
                    "fecha_enviado" => Carbon::now(),
                ]);

                Log::info("Notificación enviada exitosamente", [
                    "id" => $notificacion->id,
                    "cita" => $notificacion->idCita,
                    "tipo" => $notificacion->tipo_notificacion,
                ]);
            } else {
                $notificacion->update([
                    "estado" => "error",
                    "error_mensaje" =>
                        $resultado["error"] ?? "Error desconocido",
                ]);

                Log::error("Error al enviar notificación", [
                    "id" => $notificacion->id,
                    "error" => $resultado["error"],
                ]);
            }
        } catch (\Exception $e) {
            $notificacion->update([
                "estado" => "error",
                "error_mensaje" => $e->getMessage(),
            ]);

            Log::error(
                "Excepción al enviar notificación: " . $e->getMessage(),
                [
                    "id" => $notificacion->id,
                ],
            );
        }
    }

    /**
     * Valida si la cita sigue siendo válida para enviar notificaciones
     */
    private function validarCitaParaNotificacion(NotificationLog $notificacion)
    {
        $cita = $notificacion->cita;

        if (!$cita) {
            return false;
        }

        // No enviar si la cita está cancelada
        if (in_array($cita->estado_Cita, ["Cancelada", "No asistió"])) {
            return false;
        }

        // Para notificaciones de pago, validar que sigue sin pagar
        if ($notificacion->tipo_notificacion === "recordatorio_pago_3_horas") {
            return $cita->estado_Cita === "Sin pagar";
        }

        // Para otros recordatorios, validar que esté confirmada o pendiente
        return in_array($cita->estado_Cita, ["Confirmada", "Pendiente"]);
    }

    /**
     * Genera el mensaje según el tipo de notificación
     */
    private function generarMensaje($cita, $tipoNotificacion)
    {
        $nombrePaciente = $cita->paciente
            ? $cita->paciente->nombre
            : ($cita->prepaciente
                ? $cita->prepaciente->nombre
                : "Paciente");
        $nombrePsicologo =
            $cita->psicologo && $cita->psicologo->users
                ? $cita->psicologo->users->name .
                    " " .
                    $cita->psicologo->users->apellido
                : "tu psicólogo";

        $fechaCita = Carbon::parse($cita->fecha_cita)->format("d/m/Y");
        $horaCita = Carbon::parse($cita->hora_cita)->format("H:i");

        switch ($tipoNotificacion) {
            case "recordatorio_24_horas":
                return "🗓️ ¡Hola {$nombrePaciente}!\n\n" .
                    "Te recordamos que tienes una cita programada para MAÑANA:\n\n" .
                    "📅 Fecha: {$fechaCita}\n" .
                    "🕐 Hora: {$horaCita}\n" .
                    "👨‍⚕️ Con: {$nombrePsicologo}\n\n" .
                    "¡No olvides estar disponible! Si necesitas reagendar, contáctanos con anticipación.\n\n" .
                    "¡Te esperamos! 🌟";

            case "recordatorio_pago_3_horas":
                return "💳 ¡Hola {$nombrePaciente}!\n\n" .
                    "⚠️ RECORDATORIO DE PAGO ⚠️\n\n" .
                    "Tu cita de hoy a las {$horaCita} con {$nombrePsicologo} aún no ha sido pagada.\n\n" .
                    "⏰ Quedan menos de 3 horas para tu cita.\n\n" .
                    "Para confirmar tu asistencia, es necesario completar el pago antes de la sesión.\n\n" .
                    "Si ya realizaste el pago, por favor ignora este mensaje.\n\n" .
                    "¡Gracias! 🙏";

            case "recordatorio_1_hora":
                return "⏰ ¡Hola {$nombrePaciente}!\n\n" .
                    "Tu cita empieza en 1 HORA:\n\n" .
                    "🕐 Hora: {$horaCita}\n" .
                    "👨‍⚕️ Con: {$nombrePsicologo}\n\n" .
                    "Por favor, asegúrate de estar disponible y en un lugar tranquilo para la sesión.\n\n" .
                    "¡Nos vemos pronto! 🤝";

            case "recordatorio_30_minutos":
                return "🚨 ¡{$nombrePaciente}!\n\n" .
                    "Tu cita empieza en 30 MINUTOS:\n\n" .
                    "🕐 {$horaCita} con {$nombrePsicologo}\n\n" .
                    "¡Prepárate! La sesión comenzará muy pronto.\n\n" .
                    "¡Te esperamos! 💙";

            default:
                return "Recordatorio de cita - {$fechaCita} a las {$horaCita}";
        }
    }

    /**
     * Cancela todas las notificaciones pendientes de una cita
     */
    public function cancelarNotificacionesCita($idCita)
    {
        NotificationLog::where("idCita", $idCita)
            ->where("estado", "pendiente")
            ->update([
                "estado" => "error",
                "error_mensaje" => "Cita cancelada por el usuario",
            ]);

        Log::info("Canceladas notificaciones pendientes para cita: {$idCita}");
    }

    /**
     * Obtiene estadísticas de notificaciones
     */
    public function obtenerEstadisticas($fechaInicio = null, $fechaFin = null)
    {
        $query = NotificationLog::query();

        if ($fechaInicio) {
            $query->where("created_at", ">=", $fechaInicio);
        }

        if ($fechaFin) {
            $query->where("created_at", "<=", $fechaFin);
        }

        return [
            "total" => $query->count(),
            "enviadas" => $query->where("estado", "enviado")->count(),
            "pendientes" => $query->where("estado", "pendiente")->count(),
            "errores" => $query->where("estado", "error")->count(),
            "por_tipo" => $query
                ->select("tipo_notificacion", DB::raw("count(*) as total"))
                ->groupBy("tipo_notificacion")
                ->pluck("total", "tipo_notificacion")
                ->toArray(),
        ];
    }
}
