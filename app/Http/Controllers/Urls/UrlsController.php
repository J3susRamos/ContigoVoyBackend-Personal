<?php

namespace App\Http\Controllers\Urls;

use App\Http\Controllers\Controller;
use App\Traits\HttpResponseHelper;
use App\Models\Urls;
use Illuminate\Http\JsonResponse;

class UrlsController extends Controller
{
    /**
     * Get URLs that have enlace field with their id and name
     */
    public function getUrlsWithEnlace(): JsonResponse
    {
        try {
            $urls = Urls::whereNotNull("enlace")
                ->select("idUrls as id", "name")
                ->get();

            return HttpResponseHelper::make()
                ->successfulResponse(
                    "URLs con enlace obtenidas correctamente",
                    $urls,
                )
                ->send();
        } catch (\Exception $e) {
            return HttpResponseHelper::make()
                ->internalErrorResponse(
                    "Ocurrió un error al obtener las URLs. " . $e->getMessage(),
                )
                ->send();
        }
    }
}
