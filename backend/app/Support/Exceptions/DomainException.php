<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Base class for business-rule violations thrown from Actions.
 *
 * Laravel calls render() automatically, so Controllers never need try/catch:
 * the exception is turned into the standard { success, message, data, errors } contract.
 */
class DomainException extends RuntimeException
{
    public function __construct(string $message, private readonly int $status = 400)
    {
        parent::__construct($message, $status);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => null,
            'errors' => null,
        ], $this->status);
    }
}
