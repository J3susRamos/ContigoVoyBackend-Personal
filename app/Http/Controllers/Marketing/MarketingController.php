<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Mail\EmailMarketing;
use Illuminate\Http\Request;
use App\Models\Marketing;
use App\Models\Psicologo;
use App\Traits\HttpResponseHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class MarketingController extends Controller
{
    public function crearPlantilla(Request $request)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse('Solo los psicólogos pueden crear plantillas')
                    ->send();
            }

            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'asunto' => 'required|string|max:150',
                'remitente' => 'nullable|string|max:100',
                'destinatarios' => 'nullable|string|max:255',
                'bloques' => 'required|array',
                'bloques.*.type' => 'required|string|in:text,header,divider,image,columns',
                'bloques.*.content' => 'nullable|string',
                'bloques.*.styles' => 'sometimes|array',
                'bloques.*.styles.bold' => 'sometimes|boolean',
                'bloques.*.styles.italic' => 'sometimes|boolean',
                'bloques.*.styles.color' => 'sometimes|string',
                'bloques.*.imageUrl' => 'nullable|string',
                'bloques.*.imageUrls' => 'nullable|array',
                'bloques.*.imageUrls.*' => 'nullable|string',
            ]);
            

            $data['idPsicologo'] = $psicologo->idPsicologo;

            Marketing::create($data);

            return HttpResponseHelper::make()
                ->successfulResponse('Plantilla creada con éxito')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al crear plantilla: ' . $e->getMessage())
                ->send();
        }
    }

    public function listarPorPsicologo()
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            if (!$psicologo) {
                return HttpResponseHelper::make()
                    ->unauthorizedResponse('No autorizado')
                    ->send();
            }

            $plantillas = Marketing::where('idPsicologo', $psicologo->idPsicologo)->get();

            return HttpResponseHelper::make()
                ->successfulResponse('Plantillas obtenidas correctamente', $plantillas)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al listar plantillas: ' . $e->getMessage())
                ->send();
        }
    }

    public function detallePlantilla($id)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            $plantilla = Marketing::where('id', $id)
                ->where('idPsicologo', $psicologo->idPsicologo)
                ->first();

            if (!$plantilla) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('Plantilla no encontrada o no pertenece al usuario')
                    ->send();
            }

            return HttpResponseHelper::make()
                ->successfulResponse('Plantilla encontrada', $plantilla)
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener detalle: ' . $e->getMessage())
                ->send();
        }
    }

    public function actualizarPlantilla(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            $plantilla = Marketing::where('id', $id)
                ->where('idPsicologo', $psicologo->idPsicologo)
                ->first();

            if (!$plantilla) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('Plantilla no encontrada o no pertenece al usuario')
                    ->send();
            }

            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'asunto' => 'required|string|max:150',
                'remitente' => 'nullable|string|max:100',
                'destinatarios' => 'nullable|string|max:255',
                'bloques' => 'required|array',
                'bloques.*.type' => 'required|string|in:text,header,divider,image,columns',
                'bloques.*.content' => 'nullable|string',
                'bloques.*.styles' => 'required|array',
                'bloques.*.styles.bold' => 'required|boolean',
                'bloques.*.styles.italic' => 'required|boolean',
                'bloques.*.styles.color' => 'required|string',
                'bloques.*.imageUrl' => 'nullable|string',
                'bloques.*.imageUrls' => 'nullable|array',
                'bloques.*.imageUrls.*' => 'nullable|string',
            ]);

            $plantilla->update($data);

            return HttpResponseHelper::make()
                ->successfulResponse('Plantilla actualizada correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al actualizar plantilla: ' . $e->getMessage())
                ->send();
        }
    }

    public function eliminarPlantilla($id)
    {
        try {
            $userId = Auth::id();
            $psicologo = Psicologo::where('user_id', $userId)->first();

            $plantilla = Marketing::where('id', $id)
                ->where('idPsicologo', $psicologo->idPsicologo)
                ->first();

            if (!$plantilla) {
                return HttpResponseHelper::make()
                    ->notFoundResponse('Plantilla no encontrada o no pertenece al usuario')
                    ->send();
            }

            $plantilla->delete();

            return HttpResponseHelper::make()
                ->successfulResponse('Plantilla eliminada correctamente')
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al eliminar plantilla: ' . $e->getMessage())
                ->send();
        }
    }

    public function enviarEmail(Request $request)
{
    try {
        $userId = Auth::id();
        $psicologo = Psicologo::where('user_id', $userId)->first();

        if (!$psicologo) {
            return HttpResponseHelper::make()
                ->unauthorizedResponse('No autorizado')
                ->send();
        }

        $userEmail = $psicologo->email;  // Aquí sacas el email del psicólogo logueado

        $data = $request->validate([
            'asunto' => 'required|string|max:150',
            'destinatarios' => 'required|array',   // Ahora esperas un array de emails
            'bloques' => 'required|array',
        ]);

        $bloquesProcesados = $this->procesarBloquesConImagenes($data['bloques']);

        foreach ($data['destinatarios'] as $destinatario) {
            Mail::to($destinatario)->send(new EmailMarketing($data['asunto'], $bloquesProcesados, $userEmail));
        }


        return HttpResponseHelper::make()
            ->successfulResponse('Correos enviados correctamente')
            ->send();

    } catch (\Exception $e) {
        return HttpResponseHelper::make()
            ->internalErrorResponse('Error al enviar email: ' . $e->getMessage())
            ->send();
    }
}

public function listarEmailsPacientes()
{
    $userId = Auth::id();
    $psicologo = Psicologo::where('user_id', $userId)->first();

    if (!$psicologo) {
        return HttpResponseHelper::make()
            ->unauthorizedResponse('No autorizado')
            ->send();
    }

    $emails = $psicologo->pacientes()->pluck('email');  // Asumiendo que tienes relación Psicologo → Pacientes

    return HttpResponseHelper::make()
        ->successfulResponse('Lista de emails obtenida', $emails)
        ->send();
}

private function procesarBloquesConImagenes(array $bloques)
{
    foreach ($bloques as &$bloque) {
        if ($bloque['type'] === 'image' && isset($bloque['imageUrl']) && Str::startsWith($bloque['imageUrl'], 'data:image/')) {
            $bloque['imageUrl'] = $this->guardarImagenDesdeBase64($bloque['imageUrl']);
        }

        if ($bloque['type'] === 'columns' && isset($bloque['imageUrls']) && is_array($bloque['imageUrls'])) {
            foreach ($bloque['imageUrls'] as $key => $imageBase64) {
                if (Str::startsWith($imageBase64, 'data:image/')) {
                    $bloque['imageUrls'][$key] = $this->guardarImagenDesdeBase64($imageBase64);
                }
            }
        }
    }
    return $bloques;
}

private function guardarImagenDesdeBase64($base64)
{
    try {
        $imageData = explode(',', $base64);
        $image = base64_decode($imageData[1]);
        $extension = '';

        if (Str::contains($imageData[0], 'jpeg')) $extension = 'jpg';
        elseif (Str::contains($imageData[0], 'png')) $extension = 'png';
        elseif (Str::contains($imageData[0], 'gif')) $extension = 'gif';
        else $extension = 'png';

        $filename = 'emails/' . uniqid() . '.' . $extension;
        Storage::disk('public')->put($filename, $image);

        return asset('storage/' . $filename);
    } catch (\Exception $e) {
        \Log::error('Error guardando imagen base64: ' . $e->getMessage());
        return '';
    }
}



}


