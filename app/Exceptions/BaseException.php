<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseException extends Exception
{
    protected string $errorType = 'general_error';
    protected int $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;

    public function __construct(string $message, int|null $httpCode = null, string|null $errorType = null)
    {
        parent::__construct($message, $httpCode);

        $this->httpCode = $httpCode ?? $this->httpCode;
        $this->errorType = $errorType ?? $this->errorType;
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        $response = [
            'message' => $this->message,
            'error_type' => $this->errorType,
        ];

        if (App::hasDebugModeEnabled()) {
            $response['trace'] = $this->getLimitedTrace();
        }

        return response()->json($response, $this->httpCode);
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getErrorType(): string
    {
        return $this->errorType;
    }

    public function toArray(): array
    {
        return [
            'error' => $this->errorType,
            'message' => $this->getMessage(),
        ];
    }

    protected function getLimitedTrace(): array
    {
        return array_slice($this->getTrace(), 0, 5);
    }
}
