<?php

namespace App\Traits;

trait HttpCodesHelper
{
    /**
     * La solicitud se ha completado con éxito.
     */
    public function ok(): int
    {
        return 200;
    }

    /**
     * La solicitud se ha completado y ha resultado en la creación de un nuevo recurso.
     */
    public function created(): int
    {
        return 201;
    }

    /**
     * La solicitud se ha completado con éxito, pero su respuesta es vacía (sin contenido).
     */
    public function noContent(): int
    {
        return 204;
    }

    /**
     * La solicitud no se pudo completar debido a un error de sintaxis.
     */
    public function badRequest(): int
    {
        return 400;
    }

    /**
     * El cliente no está autorizado para solicitar un recurso.
     */
    public function unauthorized(): int
    {
        return 401;
    }

    /**
     * El cliente no tiene permiso para solicitar un recurso.
     */
    public function forbidden(): int
    {
        return 403;
    }

    /**
     * El servidor no pudo encontrar el recurso solicitado.
     */
    public function notFound(): int
    {
        return 404;
    }

    /**
     * El servidor ha encontrado un conflicto con el recurso objetivo.
     */
    public function conflict(): int
    {
        return 409;
    }

    /*
     * La solicitud no se pudo completar debido a un error sintaxis o validación.
     * @return int
     */
    public function unprocessableEntity(): int
    {
        return 422;
    }

    /*
     * El cliente ha enviado demasiadas solicitudes en un período de tiempo determinado.
     * @return int
     */
    public function tooManyRequests(): int
    {
        return 429;
    }

    /**
     * El servidor ha encontrado un error inesperado que no le permite completar la solicitud.
     */
    public function internalServerError(): int
    {
        return 500;
    }
}
