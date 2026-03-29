<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Consistent JSON envelope for API responses.
 */
final class ApiResponse
{
    /**
     * @param  array<string, mixed>|null  $errors
     */
    public static function success(mixed $data, string $message = 'OK', int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $status);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    public static function failure(
        string $message,
        int $status = JsonResponse::HTTP_BAD_REQUEST,
        ?array $errors = null,
        mixed $data = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ], $status);
    }

    public static function created(mixed $data, string $message = 'Created.'): JsonResponse
    {
        return self::success($data, $message, JsonResponse::HTTP_CREATED);
    }

    public static function paginated(LengthAwarePaginator $paginator, string $message = 'OK'): JsonResponse
    {
        return self::success([
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], $message);
    }

    /**
     * Paginated list with each row transformed through a JsonResource class.
     *
     * @param  class-string<JsonResource>  $resourceClass
     */
    public static function paginatedResources(
        Request $request,
        LengthAwarePaginator $paginator,
        string $resourceClass,
        string $message = 'OK'
    ): JsonResponse {
        $paginator->getCollection()->transform(
            fn ($model) => (new $resourceClass($model))->resolve($request)
        );

        return self::paginated($paginator, $message);
    }
}
