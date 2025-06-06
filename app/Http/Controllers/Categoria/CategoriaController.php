<?php

namespace App\Http\Controllers\Categoria;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostCategoria\PostCategoria;
use App\Models\Categoria;
use App\Traits\HttpResponseHelper;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function createCategoria(PostCategoria $request): JsonResponse
    {
        try {
            $exists = Categoria::where('nombre', $request->nombre)->exists();

            if ($exists) {
                return HttpResponseHelper::make()
                    ->internalErrorResponse('La categoria ya está registrada.') // Respuesta con error 409
                    ->send();
            }
            $categoria = Categoria::create($request->all());

            return HttpResponseHelper::make()
                ->successfulResponse('Categoria creada correctamente', [
                    'idCategoria' => $categoria->idCategoria,
                    'nombre' => $categoria->nombre
                ])
                ->send();
        } catch (\Throwable $th) {
            //throw $th;
            return HttpResponseHelper::make()
                ->internalErrorResponse('Ocurrió un problema al procesar la solicitud.')
                ->send();
        }
    }

    public function showAll(): JsonResponse
    {
        try {
            $categorias = Categoria::all();
            return HttpResponseHelper::make()
                ->successfulResponse('Categorias obtenidas', $categorias)->send();
        } catch (\Throwable $th) {
            //throw $th;
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al obtener categorias')
                ->send();
        }
    }

    public function destroyCategoria(int $id): JsonResponse
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->delete();
            return HttpResponseHelper::make()
                ->successfulResponse('Categoria eliminada correctamente')->send();
        } catch (\Throwable $th) {
            return HttpResponseHelper::make()
                ->internalErrorResponse('Error al eliminar')->send();
        }
    }
}
