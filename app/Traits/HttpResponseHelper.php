<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class HttpResponseHelper
{
    use HttpCodesHelper;

    private int $status_code = 200;

    private string $status_message;

    private string $description;

    private array|Collection $data;

    private array $errors;

    public static function make(): self
    {
        return new self;
    }

    /**
     * Construct the final response to be sent
     */
    public function send(): JsonResponse
    {
        return response()->json([
            'status_code' => $this->status_code,
            'status_message' => $this->status_message,
            'description' => $this->description,
            'result' => $this->data ?? [],
            'errorBag' => $this->errors ?? [],
        ], $this->status_code, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Return a successful response with a preconstructed 200 status code
     *
     * @return $this
     */
    public function successfulResponse(string $description, array|Collection $data = null): static
    {
        $this->setStatus($this->ok());
        $this->setStatusMessage('OK');
        $this->setDescription($description);
        $this->setData($data ?? []);

        return $this;
    }

    /**
     * Sets the HTTP status code
     *
     * @return $this
     */
    public function setStatus(int $status_code): static
    {
        $this->status_code = $status_code;

        return $this;
    }

    /**
     * Sets the HTTP status message
     *
     * @return $this
     */
    public function setStatusMessage(string $message): static
    {
        $this->status_message = $message;

        return $this;
    }

    /**
     * Sets an informative description about the response
     *
     * @return $this
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Sets the data to be sent, can also be an empty array
     *
     * @return $this
     */
    public function setData(array|Collection $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Return a not found response with a preconstructed 404 status code
     *
     * @return $this
     */
    public function notFoundResponse(string $description): static
    {
        $this->setStatus($this->notFound());
        $this->setStatusMessage('Not Found');
        $this->setDescription($description);

        return $this;
    }

    /**
     * Return an internal server error response with a preconstructed 500 status code
     *
     * @return $this
     */
    public function internalErrorResponse(string $description): static
    {
        $this->setStatus($this->internalServerError());
        $this->setStatusMessage('Internal Server Error');
        $this->setDescription($description);

        return $this;
    }

    /**
     * Return an validation error response with a preconstructed 422 status code
     *
     * @return HttpResponseHelper
     */
    public function validationErrorResponse(string $description, array $errors): static
    {
        $this->setStatus($this->unprocessableEntity());
        $this->setStatusMessage('Unprocessable Entity');
        $this->setDescription($description);
        $this->setErrorBag($errors);

        return $this;
    }

    /**
     * Sets the error bag to be sent, can also be an empty array
     *
     * @return $this
     */
    public function setErrorBag(array $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    public function forbiddenResponse(string $description): static
    {
        $this->setStatus($this->forbidden());
        $this->setStatusMessage('Forbidden');
        $this->setDescription($description);

        return $this;
    }

    public function unauthorizedResponse($message)
    {
        return response()->json([
            'message' => $message,
        ], 403);  // Código de estado HTTP 403 (Forbidden)
    }
}
