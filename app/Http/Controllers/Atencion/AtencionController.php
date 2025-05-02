<?php

namespace App\Http\Controllers\Atencion;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use Illuminate\Http\Request;
use App\Http\Requests\PostAtencion\PostAtencion;
use App\Http\Requests\PutAtencion\PutAtencion;
use App\Models\Cita;
use App\Traits\HttpResponseHelper;
use Carbon\Carbon;
use Exception;
Carbon::setLocale('es');

class AtencionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function createAtencion(PostAtencion $request, int $idCita)
    {
        try {
            $data = $request->validated();
            $data['idCita'] = $idCita;
    
            // Crear la atención
            $atencion = Atencion::create($data);
    
            // Actualizar el estado de la cita a "Confirmado"
            Cita::where('idCita', $idCita)->update(['estado_Cita' => 'Confirmada']);
    
            return HttpResponseHelper::make()
                ->successfulResponse('Atención creada y cita confirmada correctamente')
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al crear la atención: ' . $e->getMessage())
                ->send();
        }
    }

    public function showAtencionByPaciente($idPaciente)
    {
        try {
            $atencion = Atencion::with('cita.paciente')
                ->whereHas('cita', function ($query) use ($idPaciente) {$query->where('idPaciente', $idPaciente);})
                ->orderByDesc('fechaAtencion')
                ->first();

            if (!$atencion) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró ninguna atención válida para este paciente.')
                    ->send();
            }

            $fechaFormateada = Carbon::parse($atencion->fechaAtencion)->format('d/m');

            $resultado = [
                'nombre' => $atencion->cita->paciente->nombre,
                'apellido' => $atencion->cita->paciente->apellido,
                'DNI' => $atencion->cita->paciente->DNI,
                'codigo' => $atencion->cita->paciente->codigo,
                'celular' => $atencion->cita->paciente->celular,
                'edad' => $atencion->cita->paciente->edad,
                'fecha_completa' => Carbon::parse($atencion->fechaAtencion)->translatedFormat('l d \d\e F \d\e Y'),
                'fecha_atencion' => $fechaFormateada,
                'diagnostico' => $atencion->diagnostico,
                'observacion' => $atencion->observacion,
                'ultimosObjetivos' => $atencion->ultimosObjetivos,
                'comentario' => $atencion->comentario,
                'tratamiento' => $atencion->tratamiento,
                'idAtencion' => $atencion->idAtencion,
            ];

            return HttpResponseHelper::make()
                ->successfulResponse('Última atención del paciente obtenida correctamente', $resultado)
                ->send();

        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener la última atención: ' . $e->getMessage())
                ->send();
        }
    }

        /**
     * Obtener todas las atenciones 
     */
    public function showAllAtenciones()
    {
        try {
            $atenciones = Atencion::with(['cita.paciente', 'cita.prepaciente'])
                ->get()
                ->map(function ($atencion) {
                    $cita = $atencion->cita;

                    // Buscar nombre de paciente o prepaciente
                    if ($cita->paciente) {
                        $nombre = $cita->paciente->nombre . ' ' . $cita->paciente->apellido;
                    } elseif ($cita->prepaciente) {
                        $nombre = $cita->prepaciente->nombre . ' ' . $cita->prepaciente->apellido;
                    } else {
                        $nombre = 'Nombre no disponible';
                    }

                    return [
                        'hora_inicio' => $cita->hora_cita,
                        'nombre_completo' => $nombre,
                        'diagnostico' => $atencion->diagnostico,
                        'fecha_inicio' => $atencion->fechaAtencion,
                        'idCita' => $cita->idCita,
                        'idAtencion' => $atencion->idAtencion,
                        'idPaciente' => $atencion->cita->idPaciente,
                        'codigo' => $atencion->cita->paciente->codigo ?? null,
                    ];
                });

            return HttpResponseHelper::make()
                ->successfulResponse('Todas las atenciones obtenidas correctamente', [$atenciones])
                ->send();

        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener las atenciones: ' . $e->getMessage())
                ->send();
        }
    }

    /**
     * Obtener una atención por su ID.
     */
    public function showAllAtencionesPaciente(int $id)
    {
        try {
            $atenciones = Atencion::with(['cita.paciente', 'cita.prepaciente']) // Cargamos ambas relaciones
                ->whereHas('cita', function ($query) use ($id) {
                    $query->where('idPaciente', $id); // Solo filtra por paciente
                })
                ->get()
                ->map(function ($atencion) {
                    $cita = $atencion->cita;
    
                    // Si existe paciente, lo usamos. Si no, buscamos el prepaciente.
                    if ($cita->paciente) {
                        $nombre = $cita->paciente->nombre . ' ' . $cita->paciente->apellido;
                    } elseif ($cita->prepaciente) {
                        $nombre = $cita->prepaciente->nombre . ' ' . $cita->prepaciente->apellido;
                    } else {
                        $nombre = 'Nombre no disponible';
                    }
    
                    return [
                        'hora_inicio' => $cita->hora_cita,
                        'nombre_completo' => $nombre,
                        'diagnostico' => $atencion->diagnostico,
                        'fecha_inicio' => $atencion->fechaAtencion,
                        'idCita' => $cita->idCita,
                        'idAtencion' => $atencion->idAtencion
                    ];
                });
    
            return HttpResponseHelper::make()
                ->successfulResponse('Atenciones resumidas del paciente obtenidas correctamente', [$atenciones])
                ->send();
    
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener las atenciones resumidas: ' . $e->getMessage())
                ->send();
        }
    }

    /**
     * Actualizar una atención existente.
     */
    public function updateAtencion(PutAtencion $request, int $id)
    {
        try {
            $atencion = Atencion::findOrFail($id);
            if (!$atencion) {
                return response()->json(['message' => 'Atención no encontrada'], 404);
            }
            $atencion->update($request->validated());

            return HttpResponseHelper::make()
                ->successfulResponse('Atención actualizada correctamente')
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al actualizar la atención: ' . $e->getMessage())
                ->send();
        }
    }

    /**
     * Eliminar una atención.
     */
    public function destroyAtencion(int $id)
    {
        try {
            $atencion = Atencion::findOrFail($id);
            $atencion->delete();

            return HttpResponseHelper::make()
                ->successfulResponse('Atención eliminada correctamente')
                ->send();
        } catch (Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al eliminar la atención: ' . $e->getMessage())
                ->send();
        }
    }
}
