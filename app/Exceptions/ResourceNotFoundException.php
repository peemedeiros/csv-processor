<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ResourceNotFoundException extends BaseException
{
    protected string $errorType = 'resource_not_found';
    protected int $httpCode = Response::HTTP_NOT_FOUND;

    public function __construct(string $message = 'The requested resource was not found.')
    {
        parent::__construct($message, $this->httpCode, $this->errorType);
    }
}