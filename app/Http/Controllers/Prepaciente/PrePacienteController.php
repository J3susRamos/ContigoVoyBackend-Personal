<?php

namespace App\Http\Controllers\Prepaciente;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarConfirmacionCitaCorreo;
use Illuminate\Http\Request;
use App\Models\PrePaciente;
use App\Mail\PrePacienteCreado;
use App\Models\Cita;
use App\Traits\HttpResponseHelper;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Jobs\EnviarNotificacionesPrePaciente;
use App\Jobs\EnviarRecordatorioCita;
use App\Jobs\EnviarConfirmacionCitaWhatsApp;
use Carbon\Carbon;


class PrePacienteController extends Controller
{
    public function createPrePaciente(Request $request): JsonResponse
    {
        try {
            $prePacienteValidated = $request->validate([
                "nombre" => "required|string|max:150",
                "celular" => "required|string|min:3|max:30",
                "correo" => "required|email|max:150",
                "idPsicologo" => "required|exists:psicologos,idPsicologo",
            ]);

            $prePaciente = PrePaciente::create($prePacienteValidated);
            $id = $prePaciente->idPrePaciente;

            // Validar cita
            $citaValidated = $request->validate([
                "idPsicologo" => "required|exists:psicologos,idPsicologo",
                "fecha_cita" => "required|date",
                "hora_cita" => "required|date_format:H:i",
                "enfoque" => "required|in:niños,adolescentes,familiar,pareja,adulto",
            ]);

              $mapeoEnfoques = [
                'niños' => 'Niños',
                'adolescentes' => 'Adolescentes',
                'familiar' => 'Familiar',
                'pareja' => 'Pareja',
                'adulto' => 'Adulto',
            ];

            $motivo = ($mapeoEnfoques[$request->enfoque]);

            // Set defaults de la cita
            $citaData = array_merge($citaValidated, [
                "motivo_Consulta" => "$motivo",
                "idPrePaciente" => $id,
                "jitsi_url" => "https://meet.jit.si/consulta_" . uniqid(),
                "fecha_limite" => Carbon::parse($request->fecha_cita)->subDays(1)->format('Y-m-d'),
            ]);

            $cita = Cita::create($citaData);
            $cita->refresh(); // Recargar desde BD para asegurar que jitsi_url esté presente

            $horaRecordatorio = Carbon::parse($cita->fecha_cita . ' ' . $cita->hora_cita)
                ->subHours(3);
            EnviarConfirmacionCitaWhatsApp::dispatch($cita);
            EnviarConfirmacionCitaCorreo::dispatch($cita);
            Log::info('Jobs dispatched: WhatsApp y Correo para cita', ['cita_id' => $cita->idCita]);
            EnviarRecordatorioCita::dispatch($cita)->delay($horaRecordatorio);

            // Cargamos la relación con el psicólogo
            $prePaciente = PrePaciente::with("psicologo.users")->find($id);

            $datos = [
                "nombre" => $prePaciente->nombre,
                "celular" => $prePaciente->celular,
                "correo" => $prePaciente->correo,
                "estado" => $prePaciente->estado,
            ];

            $adminEmail = config(
                "emails.admin_address",
                "contigovoyproject@gmail.com",
            );
            $nombrePsicologo =
                $prePaciente->psicologo && $prePaciente->psicologo->users
                ? $prePaciente->psicologo->users->name . " " . $prePaciente->psicologo->users->apellido
                : "tu psicólogo asignado";


            EnviarNotificacionesPrePaciente::dispatch(
                $prePaciente,
                $datos,
                $request->input("fecha_cita"),
                $request->input("hora_cita"),
                $nombrePsicologo,
                $cita->idCita,
            );

            \Illuminate\Support\Facades\Log::info('Cita creada con jitsi_url:', [
                'idCita' => $cita->idCita,
                'jitsi_url' => $cita->jitsi_url,
            ]);

            return HttpResponseHelper::make()
                ->successfulResponse(
                    "PrePaciente creado correctamente y correo enviado.",
                )
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un problema al procesar la solicitud: " .
                        $e->getMessage(),
                )
                ->send();
        }
    }

    /**
     * Obtener todos los pre pacientes.
     */
    public function showAllPrePacientes(): View|JsonResponse
    {
        try {
            $prePacientes = PrePaciente::all();

            return view("pre_pacientes", ["prePacientes" => $prePacientes]);
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Error al obtener los pre pacientes: " . $e->getMessage(),
                )
                ->send();
        }
    }

    /**
     * Obtener un pre paciente por su ID.
     */
    public function showPrePaciente(int $id): JsonResponse
    {
        try {
            $prePaciente = PrePaciente::findOrFail($id);

            return HttpResponseHelper::make()
                ->successfulResponse("PrePaciente obtenido correctamente", [
                    $prePaciente,
                ])
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Error al obtener el pre paciente: " . $e->getMessage(),
                )
                ->send();
        }
    }

    /**
     * Actualizar un pre paciente existente.
     */
    public function updatePrePaciente(Request $request, int $id): JsonResponse
    {
        try {
            $prePaciente = PrePaciente::findOrFail($id);
            $prePaciente->update(
                $request->validate([
                    "nombre" => "required|string|max:150",
                    "celular" => "required|string|min:3|max:30",
                    "correo" =>
                    "required|email|unique:pre_pacientes,correo|max:150",
                ]),
            );

            return HttpResponseHelper::make()
                ->successfulResponse("PrePaciente actualizado correctamente")
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Error al actualizar el pre paciente: " . $e->getMessage(),
                )
                ->send();
        }
    }

    /**
     * Eliminar un pre paciente.
     */
    public function destroyPrePaciente(int $id): JsonResponse
    {
        try {
            $prePaciente = PrePaciente::findOrFail($id);
            $prePaciente->delete();

            return HttpResponseHelper::make()
                ->successfulResponse("PrePaciente eliminado correctamente")
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Error al eliminar el pre paciente: " . $e->getMessage(),
                )
                ->send();
        }
    }
}
