<?php
namespace App\Http\Controllers\Idioma;

use App\Http\Controllers\Controller;
use App\Models\Idioma;
use App\Traits\HttpResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdiomaController extends Controller
{
    // Normalización consistente con backfill
    private function norm(string $s): string {
        $s = trim($s);
        $s = mb_strtolower($s);
        return mb_convert_case($s, MB_CASE_TITLE, "UTF-8");
    }

    public function index(): JsonResponse {
        $idiomas = Idioma::orderBy('nombre')->get(['idIdioma','nombre']);
        return HttpResponseHelper::make()
            ->successfulResponse('Idiomas obtenidos correctamente', $idiomas)
            ->send();
    }

    public function store(Request $request): JsonResponse {
        $request->validate(['nombre' => 'required|string|max:100']);
        $nombre = $this->norm($request->input('nombre'));

        $idioma = Idioma::firstOrCreate(['nombre' => $nombre]);
        return HttpResponseHelper::make()
            ->successfulResponse('Idioma creado correctamente', $idioma)
            ->send();
    }

    public function update(Request $request, int $id): JsonResponse {
        $idioma = Idioma::findOrFail($id);
        $request->validate(['nombre' => 'required|string|max:100']);
        $nombre = $this->norm($request->input('nombre'));

        // Unicidad manual en caso de colaciones raras
        if (Idioma::where('nombre',$nombre)->where('idIdioma','<>',$id)->exists()) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ya existe un idioma con ese nombre')
                ->send();
        }

        $idioma->update(['nombre' => $nombre]);
        return HttpResponseHelper::make()
            ->successfulResponse('Idioma actualizado correctamente')
            ->send();
    }

    public function destroy(int $id): JsonResponse {
        $idioma = Idioma::findOrFail($id);
        $idioma->delete();
        return HttpResponseHelper::make()
            ->successfulResponse('Idioma eliminado correctamente')
            ->send();
    }
}
