<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class FormValidationException extends BaseException
{
    protected string $errorType = 'form_validation_error';
    protected int $httpCode = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected array $errors;

    public function __construct(ValidationException $exception)
    {
        $this->errors = $exception->errors();
        parent::__construct($exception->getMessage(), $this->httpCode, $this->errorType);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        $response = [
            'message' => $this->message,
            'error_type' => $this->errorType,
            'errors' => $this->errors,
        ];

        return response()->json($response, $this->httpCode);
    }
}
