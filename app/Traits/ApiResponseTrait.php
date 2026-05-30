<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

trait ApiResponseTrait
{
    /**
     * Return a standardized success response.
     */
    protected function successResponse(mixed $data = null, ?string $message = 'Operation completed successfully.', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
            'meta'    => [
                'timestamp'   => time(),
                'api_version' => 'v1',
            ]
        ], $statusCode);
    }

    /**
     * Return a standardized error response.
     */
    protected function errorResponse(string $message, int $statusCode = 400, ?array $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
            'meta'    => [
                'timestamp'   => time(),
                'api_version' => 'v1',
            ]
        ], $statusCode);
    }

    /**
     * Return a standardized validation error response.
     */
    protected function validationErrorResponse(array|Validator $validationErrors): JsonResponse
    {
        $errors = $validationErrors instanceof Validator 
            ? $validationErrors->errors()->toArray() 
            : $validationErrors;

        return $this->errorResponse('Validation failed.', 422, $errors);
    }
}
