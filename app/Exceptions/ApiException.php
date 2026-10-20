<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/** Exception bisnis yang langsung dirender sebagai respons JSON. */
class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private int $status = 400,
        private array $errors = [],
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), $this->status, $this->errors);
    }
}
