<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * ApiResponseTrait
 *
 * Provides a single, consistent JSON response contract for all API controllers.
 * Every response — success or failure — must go through one of these methods.
 *
 * Contract shape:
 * {
 *   "success": bool,
 *   "message": string,
 *   "data": mixed|null,
 *   "errors": object|null,
 *   "meta": object|null
 * }
 */
trait ApiResponseTrait
{
    /**
     * Return a successful JSON response.
     *
     * Use this for: 200 OK, 201 Created
     *
     * @param  mixed       $data     The payload to return (object, array, Resource, etc.)
     * @param  string      $message  Human-readable success message
     * @param  int         $status   HTTP status code (default 200)
     * @param  array       $meta     Optional pagination or extra metadata
     */
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a 201 Created response.
     *
     * Convenience wrapper around success() for resource creation.
     */
    protected function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return a generic error response.
     *
     * Use this for: 401 Unauthorized, 403 Forbidden, 404 Not Found, 500 Server Error
     *
     * @param  string  $message  Human-readable error description
     * @param  int     $status   HTTP status code
     * @param  mixed   $errors   Optional structured error details
     */
    protected function error(
        string $message = 'An error occurred',
        int $status = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $status);
    }

    /**
     * Return a 422 Unprocessable Entity response for validation failures.
     *
     * Shaped to match Laravel's ValidationException structure so the
     * frontend Axios interceptor can handle all validation errors uniformly.
     *
     * @param  array   $errors   Field-level validation errors (field => [messages])
     * @param  string  $message  Top-level error message
     */
    protected function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], 422);
    }

    /**
     * Return a 401 Unauthenticated response.
     */
    protected function unauthenticated(string $message = 'Unauthenticated'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Return a 403 Forbidden response.
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Return a 404 Not Found response.
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }
}
