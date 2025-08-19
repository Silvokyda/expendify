<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    protected function success(mixed $data = null, string $message = 'OK', int $code = 200, array $meta = []): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'meta'    => (object) $meta, // always present, empty object if none
        ], $code);
    }

    protected function created(mixed $data = null, string $message = 'Created', array $meta = []): JsonResponse
    {
        return $this->success($data, $message, 201, $meta);
    }

    protected function noContent(string $message = 'No Content'): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => null,
            'meta'    => (object)[],
        ], 204);
    }

    protected function error(string $message = 'Error', int $code = 400, mixed $data = null, array $meta = []): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'data'    => $data,
            'meta'    => (object)$meta,
        ], $code);
    }

    protected function notFound(string $message = 'Not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    protected function validationFailed(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, 422, ['errors' => $errors]);
    }

    protected function paginateResponse($paginator, $itemsTransformer = null, string $message = 'OK'): JsonResponse
    {
        $items = $itemsTransformer ? $paginator->getCollection()->map($itemsTransformer)->values() : $paginator->items();

        return $this->success(
            $items,
            $message,
            200,
            [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ]
        );
    }
}
