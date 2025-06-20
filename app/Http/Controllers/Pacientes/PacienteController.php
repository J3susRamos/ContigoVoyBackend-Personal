<?php

namespace App\Http\Controllers\Pacientes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostPaciente\PostPaciente;
use App\Http\Requests\PostUser\PostUser;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Psicologo;
use App\Traits\HttpResponseHelper;
use Carbon\Carbon;
use Illuminate\Container\Attributes\DB;
use Illuminate\Support\Facades\Auth;

class PacienteController extends Controller
{
    public function createPaciente(PostPaciente $requestPaciente)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse('Solo los psicólogos pueden crear pacientes')
                    ->send();
            }

            $pacienteData = $requestPaciente->all();
            $pacienteData['fecha_nacimiento'] = Carbon::createFromFormat('d / m / Y', $pacienteData['fecha_nacimiento'])->format('Y-m-d');
            $pacienteData['idPsicologo'] = $psicologo->idPsicologo;
            $pacienteData['codigo'] = Paciente::generatePacienteCode();

            Paciente::create($pacienteData);

            return HttpResponseHelper::make()
                ->successfulResponse('Paciente creado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrio un problema al procesar la solicitud.' .
                    $e->getMessage())
                ->send();
        }
    }

    // Conteo de citas por paciente
    public function getCitasPaciente(int $idPaciente)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse('No se tiene acceso como psicólogo.')
                    ->send();
            }

            $paciente = Paciente::where('idPaciente', $idPaciente)
                ->where('idPsicologo', $psicologo->idPsicologo)
                ->first();

            if (!$paciente) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('El paciente no pertenece al psicólogo autenticado.')
                    ->send();
            }

            // Contar las citas del paciente por estado
            $citasPendientes = Cita::where('idPaciente', $idPaciente)
                ->where('estado_Cita', 'Pendiente')
                ->count();

            $citasCanceladas = Cita::where('idPaciente', $idPaciente)
                ->where('estado_Cita', 'Cancelada')
                ->count();

            $citasConfirmadas = Cita::where('idPaciente', $idPaciente)
                ->where('estado_Cita', 'Confirmada')
                ->count();

            $response = [
                'pendientes' => $citasPendientes,
                'canceladas' => $citasCanceladas,
                'confirmadas' => $citasConfirmadas,
            ];

            return HttpResponseHelper::make()
                ->successfulResponse('Conteo de citas del paciente obtenido correctamente', $response)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function updatePaciente(PostPaciente $requestPaciente, int $id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $pacienteData = $requestPaciente->all();
            $pacienteData['fecha_nacimiento'] = Carbon::createFromFormat('d / m / Y', $pacienteData['fecha_nacimiento'])->format('Y-m-d');
            $paciente->update($pacienteData);

            return HttpResponseHelper::make()
                ->successfulResponse('Paciente actualizado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrio un problema al procesar la solicitud.' . $e->getMessage())
                ->send();
        }
    }

    public function showPacientesByPsicologo()
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se tiene acceso como psicologo')
                    ->send();
            }

            // $pacientes = Paciente::where('idPsicologo', $psicologo->idPsicologo)->get();
            $pacientes = Paciente::where('idPsicologo', $psicologo->idPsicologo)
                ->with(['citas' => function ($query) {
                    $query->orderBy('fecha_cita', 'desc')
                        ->orderBy('hora_cita', 'desc')
                        ->limit(1);
                }])
                ->get();

            $response = $pacientes->map(function ($paciente) {
                $ultimaCita = $paciente->citas->first();
                return [
                    'idPaciente' => $paciente->idPaciente,
                    'codigo' => $paciente->codigo,
                    'DNI' => $paciente->DNI,
                    'nombre' => $paciente->nombre . ' ' . $paciente->apellido,
                    'email' => $paciente->email,
                    'celular' => $paciente->celular,
                    'genero' => $paciente->genero,
                    'fecha_nacimiento' => $paciente->fecha_nacimiento,
                    'edad' => Carbon::parse($paciente->fecha_nacimiento)->age,
                    'ultima_cita_fecha' => $ultimaCita ? $ultimaCita->fecha_cita . ' ' . $ultimaCita->hora_cita : null
                ];
            });

            return HttpResponseHelper::make()
                ->successfulResponse('Pacientes obtenidos correctamente', $response)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function showPacienteById($id)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se tiene acceso como psicólogo.')
                    ->send();
            }

            $paciente = Paciente::where('idPaciente', $id)
                ->where('idPsicologo', $psicologo->idPsicologo)
                ->first();

            return HttpResponseHelper::make()
                ->successfulResponse('Paciente obtenido correctamente', $paciente->toArray())
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function destroyPaciente(int $id)
    {
        try {
            $paciente = Paciente::findOrFail($id);
            $paciente->delete();

            return HttpResponseHelper::make()
                ->successfulResponse('Paciente eliminado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al eliminar el blog: ' . $e->getMessage())
                ->send();
        }
    }

    public function getPacientesGenero()
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse('No se tiene acceso como psicólogo.')
                    ->send();
            }

            // Obtener el conteo de pacientes por género
            $pacientes = Paciente::where('idPsicologo', $psicologo->idPsicologo)->get();
            $total = $pacientes->count();
            if ($total === 0) {
                return HttpResponseHelper::make()
                    ->successfulResponse('No hay pacientes registrados.', [])
                    ->send();
            }
            $estadisticas = $pacientes->groupBy(function ($paciente) {
                return $paciente->genero ?: 'Desconocido'; // Agrupar por género, o 'Desconocido' si no se especifica
            })->map(function ($items) use ($total) {
                return [
                    'cantidad' => $items->count(),
                    'porcentaje' => round(($items->count() / $total) * 100)
                ];
            });

            return HttpResponseHelper::make()
                ->successfulResponse('Porcentaje de pacientes por genero obtenido correctamente', $estadisticas)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function getPacientesEdad()
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse('No se tiene acceso como psicólogo.')
                    ->send();
            }

            // Obtener el conteo de pacientes por rango de edad
            $estadisticas = Paciente::where('idPsicologo', $psicologo->idPsicologo)
                ->get()
                ->groupBy(function ($paciente) {
                    $edad = Carbon::parse($paciente->fecha_nacimiento)->age;
                    if ($edad <= 12) {
                        return '0-12';
                    } elseif ($edad <= 17) {
                        return '13-17';
                    } elseif ($edad <= 24) {
                        return '18-24';
                    } elseif ($edad <= 34) {
                        return '25-34';
                    } elseif ($edad <= 44) {
                        return '35-44';
                    } else {
                        return '45+';
                    }
                })
                ->map(function ($pacientes) {
                    return $pacientes->count();
                });

            return HttpResponseHelper::make()
                ->successfulResponse('Estadísticas de pacientes por rango de edad obtenidas correctamente', $estadisticas)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function getPacientesLugar()
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse('No se tiene acceso como psicólogo.')
                    ->send();
            }

            $estadisticas = Paciente::where('idPsicologo', $psicologo->idPsicologo)
                ->get()
                ->groupBy(function ($paciente) {
                    // Extraer solo el último texto después de la última coma
                    $direccion = $paciente->direccion;
                    $partes = explode(',', $direccion);
                    return trim(end($partes)); // Obtener la última parte y eliminar espacios
                })
                ->map(function ($pacientes) {
                    return $pacientes->count();
                });

            return HttpResponseHelper::make()
                ->successfulResponse('Estadísticas de pacientes por lugar obtenidas correctamente', $estadisticas)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }
}
