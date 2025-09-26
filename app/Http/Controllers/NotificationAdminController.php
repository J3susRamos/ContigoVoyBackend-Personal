<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AutomatedNotificationService;
use App\Models\NotificationLog;
use App\Models\Cita;
use Carbon\Carbon;
use App\Traits\HttpResponseHelper;
use Illuminate\Support\Facades\Log;

class NotificationAdminController extends Controller
{
    private $notificationService;

    public function __construct(
        AutomatedNotificationService $notificationService,
    ) {
        $this->notificationService = $notificationService;
    }

    /**
     * Obtener estadísticas de notificaciones
     */
    public function getStats(Request $request)
    {
        try {
            $fechaInicio = $request->input(
                "fecha_inicio",
                Carbon::today()->format("Y-m-d"),
            );
            $fechaFin = $request->input(
                "fecha_fin",
                Carbon::tomorrow()->format("Y-m-d"),
            );

            $stats = $this->notificationService->obtenerEstadisticas(
                $fechaInicio,
                $fechaFin,
            );

            return response()->json([
                "success" => true,
                "data" => $stats,
                "periodo" => [
                    "inicio" => $fechaInicio,
                    "fin" => $fechaFin,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Error al obtener estadísticas: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Listar notificaciones con paginación
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input("per_page", 15);
            $estado = $request->input("estado");
            $tipo = $request->input("tipo_notificacion");
            $fecha = $request->input("fecha");

            $query = NotificationLog::with(
                "cita.paciente",
                "cita.prepaciente",
            )->orderBy("fecha_programada", "desc");

            if ($estado) {
                $query->where("estado", $estado);
            }

            if ($tipo) {
                $query->where("tipo_notificacion", $tipo);
            }

            if ($fecha) {
                $query->whereDate("fecha_programada", $fecha);
            }

            $notifications = $query->paginate($perPage);

            // Transform data for response
            $notifications
                ->getCollection()
                ->transform(function ($notification) {
                    $pacienteNombre = $notification->cita->paciente
                        ? $notification->cita->paciente->nombre .
                            " " .
                            $notification->cita->paciente->apellido
                        : ($notification->cita->prepaciente
                            ? $notification->cita->prepaciente->nombre
                            : "N/A");

                    return [
                        "id" => $notification->id,
                        "cita_id" => $notification->idCita,
                        "paciente" => $pacienteNombre,
                        "telefono" => $notification->telefono,
                        "tipo_notificacion" => $notification->tipo_notificacion,
                        "estado" => $notification->estado,
                        "fecha_programada" => $notification->fecha_programada,
                        "fecha_enviado" => $notification->fecha_enviado,
                        "whatsapp_message_id" =>
                            $notification->whatsapp_message_id,
                        "error_mensaje" => $notification->error_mensaje,
                        "fecha_cita" => $notification->cita->fecha_cita ?? null,
                        "hora_cita" => $notification->cita->hora_cita ?? null,
                    ];
                });

            return response()->json([
                "success" => true,
                "data" => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Error al obtener notificaciones: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Reenviar una notificación fallida
     */
    public function resend(Request $request, $id)
    {
        try {
            $notification = NotificationLog::with("cita")->findOrFail($id);

            if ($notification->estado === "enviado") {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Esta notificación ya fue enviada exitosamente",
                    ],
                    400,
                );
            }

            // Reset status to pending and let the cron job handle it
            $notification->update([
                "estado" => "pendiente",
                "error_mensaje" => null,
                "fecha_enviado" => null,
            ]);

            return response()->json([
                "success" => true,
                "message" => "Notificación marcada para reenvío",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Error al reenviar notificación: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Programar notificaciones manualmente para una cita específica
     */
    public function scheduleForAppointment(Request $request, $citaId)
    {
        try {
            $cita = Cita::findOrFail($citaId);

            $result = $this->notificationService->programarNotificacionesPorCita(
                $citaId,
            );

            if ($result) {
                return response()->json([
                    "success" => true,
                    "message" =>
                        "Notificaciones programadas exitosamente para la cita",
                ]);
            } else {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "No se pudieron programar las notificaciones",
                    ],
                    400,
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Error al programar notificaciones: " .
                        $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Cancelar notificaciones pendientes de una cita
     */
    public function cancelForAppointment($citaId)
    {
        try {
            $this->notificationService->cancelarNotificacionesCita($citaId);

            return response()->json([
                "success" => true,
                "message" => "Notificaciones canceladas exitosamente",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Error al cancelar notificaciones: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Procesar notificaciones pendientes manualmente
     */
    public function processNow(Request $request)
    {
        try {
            $this->notificationService->procesarNotificacionesPendientes();

            return response()->json([
                "success" => true,
                "message" => "Notificaciones procesadas exitosamente",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Error al procesar notificaciones: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Dashboard con métricas en tiempo real
     */
    public function dashboard()
    {
        try {
            $today = Carbon::today();
            $tomorrow = Carbon::tomorrow();

            // Estadísticas del día
            $dailyStats = $this->notificationService->obtenerEstadisticas(
                $today,
                $tomorrow,
            );

            // Notificaciones próximas (próximas 2 horas)
            $proximasNotificaciones = NotificationLog::with(
                "cita.paciente",
                "cita.prepaciente",
            )
                ->where("estado", "pendiente")
                ->whereBetween("fecha_programada", [
                    Carbon::now(),
                    Carbon::now()->addHours(2),
                ])
                ->orderBy("fecha_programada")
                ->take(10)
                ->get();

            // Errores recientes (últimas 24 horas)
            $erroresRecientes = NotificationLog::with(
                "cita.paciente",
                "cita.prepaciente",
            )
                ->where("estado", "error")
                ->where("created_at", ">=", Carbon::now()->subHours(24))
                ->orderBy("created_at", "desc")
                ->take(10)
                ->get();

            return response()->json([
                "success" => true,
                "data" => [
                    "estadisticas_diarias" => $dailyStats,
                    "proximas_notificaciones" => $proximasNotificaciones,
                    "errores_recientes" => $erroresRecientes,
                    "timestamp" => Carbon::now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Error al obtener dashboard: " . $e->getMessage(),
                ],
                500,
            );
        }
    }
}
