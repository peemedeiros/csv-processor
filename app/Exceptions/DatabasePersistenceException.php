<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class DatabasePersistenceException extends BaseException
{
    protected string $errorType = 'database_persistence_error';
    protected int $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;

    protected string $sqlErrorMessage;

    public function __construct(string $message = 'Data persistence failure', string $sqlErrorMessage = '')
    {
        $this->sqlErrorMessage = $sqlErrorMessage;

        parent::__construct($message, $this->httpCode, $this->errorType);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        $response = [
            'message' => $this->message,
            'error_type' => $this->errorType,
        ];

        if (App::hasDebugModeEnabled()) {
            $response['sql_error_details'] = $this->sqlErrorMessage;
        }

        return response()->json($response, $this->httpCode);
    }
}
