<?php

namespace App\Http\Controllers\Psicologos;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostUser\PostUser;
use App\Http\Requests\PostPsicologo\PostPsicologo;
use App\Http\Requests\PutPsicologo\PutPsicologo;
use App\Http\Requests\PutUser\PutUser;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Psicologo;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Traits\HttpResponseHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class PsicologosController extends Controller
{

    public function createPsicologo(PostPsicologo $requestPsicologo, PostUser $requestUser): JsonResponse
    {
        try {
            $usuarioData = $requestUser->all();
            $usuarioData['rol'] = 'PSICOLOGO';
            $usuarioData['fecha_nacimiento'] = Carbon::createFromFormat('d / m / Y', $usuarioData['fecha_nacimiento'])
                ->format('Y-m-d');
            $usuarioData['password'] = Hash::make($requestUser['password']);

            $usuario = User::create($usuarioData);
            $usuario_id = $usuario->user_id;

            // Asignar el user_id recién creado al psicólogo
            $psicologoData = $requestPsicologo->all();
            $psicologoData['user_id'] = $usuario_id;
            $psicologo = Psicologo::create($psicologoData);

            // Asociar las especialidades y enfoques al psicólogo
            $psicologo->especialidades()->attach($requestPsicologo->input('especialidades'));

            $usuario->assignRole('PSICOLOGO');

            return HttpResponseHelper::make()
                ->successfulResponse('Psicólogo creado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud. ' . $e->getMessage())
                ->send();
        }
    }

    public function showById(int $id): JsonResponse
    {
        try {
            $psicologo = Psicologo::with(['especialidades', 'users'])->find($id);

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un psicólogo con el ID proporcionado.')
                    ->send();
            }

            $response = [
                // se modifico 'Titulo' a 'titulo'
                'idPsicologo' => $psicologo->idPsicologo,
                'titulo' => $psicologo->titulo,
                'nombre' => $psicologo->users->name,
                'apellido' => $psicologo->users->apellido,
                'pais' => $psicologo->pais,
                'genero' => $psicologo->genero,
                'correo' => $psicologo->users->email,
                'contraseña' => $psicologo->users->password,
                'imagen' => $psicologo->users->imagen,
                'fecha_nacimiento' => $psicologo->users->fecha_nacimiento->format('d/m/Y'),
                'especialidades' => $psicologo->especialidades->pluck('nombre'),
                'introduccion' => $psicologo->introduccion,
                'experiencia' => $psicologo->experiencia,
            ];

            return HttpResponseHelper::make()
                ->successfulResponse('Psicólogos obtenidos correctamente', $response)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener el psicólogo: ' . $e->getMessage())
                ->send();
        }
    }

    public function showAllPsicologos(): JsonResponse
    {
        try {
            $psicologos = Psicologo::with(['especialidades', 'users'])
                ->where('estado', 'A')
                ->get()
                ->map(function ($psicologo) {
                    return [
                        // se modifico 'Titulo' a 'titulo'
                        'idPsicologo' => $psicologo->idPsicologo,
                        'titulo' => $psicologo->titulo,
                        'nombre' => $psicologo->users->name,
                        'apellido' => $psicologo->users->apellido,
                        'pais' => $psicologo->pais,
                        'edad' => $psicologo->users->edad,
                        'genero' => $psicologo->genero,
                        'experiencia' => $psicologo->experiencia,
                        'especialidades' => $psicologo->especialidades->pluck('nombre'),
                        'introduccion' => $psicologo->introduccion,
                        'horario' => $psicologo->horario,
                        'correo' => $psicologo->users->email,
                        'imagen' => $psicologo->users->imagen,
                    ];
                });

            return HttpResponseHelper::make()
                ->successfulResponse('Lista de psicologos obtenida correctamente', $psicologos)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al obtener los psicologos: ' . $e->getMessage())
                ->send();
        }
    }

    public function updatePsicologo(PutPsicologo $requestPsicologo, PutUser $requestUser, int $id): JsonResponse
    {
        try {
            $psicologo = Psicologo::findOrFail($id);
            $usuario = User::findOrFail($psicologo->user_id);
            $psicologoData = $requestPsicologo->only([
                'titulo',
                'introduccion',
                'pais',
                'genero',
                'experiencia',
                'horario'
            ]);
            $psicologo->update($psicologoData);

            $usuarioData = $requestUser->only(['name', 'apellido', 'email', 'password', 'fecha_nacimiento', 'imagen']);
            if ($requestUser->filled('password')) {
                $usuarioData['password'] = Hash::make($requestUser->password);
            }
            if ($requestUser->filled('fecha_nacimiento')) {
                $usuarioData['fecha_nacimiento'] = Carbon::createFromFormat('d/m/Y', $requestUser->fecha_nacimiento)->format('Y-m-d');
            }
            $usuario->update($usuarioData);
            if ($requestPsicologo->filled('especialidades')) {
                $especialidadesNombres = $requestPsicologo->input('especialidades');
                $especialidadesIds = [];
                foreach ($especialidadesNombres as $nombre) {
                    $nombre = trim($nombre);
                    if (empty($nombre)) {
                        continue;
                    }
                    $especialidad = Especialidad::firstOrCreate(['nombre' => $nombre]);
                    if (!$especialidad->idEspecialidad) {
                        throw new \Exception("No se pudo crear o encontrar la especialidad: $nombre");
                    }
                    $especialidadesIds[] = $especialidad->idEspecialidad;
                }
                if (!empty($especialidadesIds)) {
                    $psicologo->especialidades()->sync($especialidadesIds);
                }
            }

            return HttpResponseHelper::make()
                ->successfulResponse('Psicólogo actualizado correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema: ' . $e->getMessage())
                ->send();
        }
    }

    public function cambiarEstadoPsicologo(int $id): JsonResponse
    {
        try {
            $psicologo = Psicologo::find($id);

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('No se encontró un psicólogo con el ID proporcionado.')
                    ->send();
            }

            $psicologo->estado = $psicologo->estado === 'I' ? 'A' : 'I';
            $psicologo->save();

            return HttpResponseHelper::make()
                ->successfulResponse('Estado del psicólogo cambiado correctamente a ' . ($psicologo->estado === 'A' ? 'Activo' : 'Inactivo'))
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al cambiar el estado del psicólogo: ' . $e->getMessage())
                ->send();
        }
    }

    public function psicologoDashboard(): JsonResponse
    {
        $userId = Auth::id();
        $psicologo = Psicologo::where('user_id', $userId)->first();

        if (!$psicologo) {
            return HttpResponseHelper::make()
                ->notFoundResponse('No se encontró un psicólogo asociado a este usuario.')
                ->send();
        }

        $idPsicologo = $psicologo->idPsicologo;

        // Obtener citas del psicólogo
        $totalCitas = Cita::where('idPsicologo', $idPsicologo)->count();
        $citasCompletadas = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'completada')->count();
        $citasPendientes = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'pendiente')->count();
        $citasCanceladas = Cita::where('idPsicologo', $idPsicologo)->where('estado_Cita', 'cancelada')->count();

        $totalMinutosReservados = Cita::where('idPsicologo', $idPsicologo)
        ->whereIn('estado_Cita', ['completada', 'pendiente'])
        ->sum('duracion');

        // Total de pacientes únicos
        $totalPacientes = Cita::where('idPsicologo', $idPsicologo)
        ->whereNotNull('idPaciente')
        ->distinct('idPaciente')
        ->count('idPaciente');

        // Nuevos pacientes en los últimos 30 días (por su primera cita)
        $nuevosPacientes = Cita::select('idPaciente')
        ->where('idPsicologo', $idPsicologo)
        ->whereNotNull('idPaciente')
        ->selectRaw('MIN(fecha_Cita) as primera_cita, idPaciente')
        ->groupBy('idPaciente')
        ->havingRaw('primera_cita >= ?', [now()->subDays(30)])
        ->get()
        ->count();

        return HttpResponseHelper::make()
            ->successfulResponse('Datos del dashboard cargados correctamente',[
               'total_citas' => $totalCitas,
            'citas_completadas' => $citasCompletadas,
            'citas_pendientes' => $citasPendientes,
            'citas_canceladas' => $citasCanceladas,
            'total_minutos_reservados' => $totalMinutosReservados,
            'total_pacientes' => $totalPacientes,
            'nuevos_pacientes' => $nuevosPacientes,
            ])
            ->send();
    }

    public function DeletePsicologo(int $id): JsonResponse
    {
        try {

            $psicologo = Psicologo::find($id);
            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('Psicólogo no encontrado')
                    ->send();
            }
            $user_id = $psicologo->user_id;
            $psicologo->delete();
            if ($user_id) {
                User::find($user_id)->delete();
            }

            return HttpResponseHelper::make()
                ->successfulResponse('Psicólogo y usuario eliminados correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al eliminar el psicólogo: ' . $e->getMessage())
                ->send();
        }
    }
}
