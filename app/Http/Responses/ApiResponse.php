<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final readonly class ApiResponse
{
    public static function success(string $message, mixed $data = null, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function paginated(string $message, AnonymousResourceCollection $resource): JsonResponse
    {
        $payload = $resource->response()->getData(true);

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $payload['data'],
            'links' => $payload['links'],
            'meta' => $payload['meta'],
        ]);
    }
}
