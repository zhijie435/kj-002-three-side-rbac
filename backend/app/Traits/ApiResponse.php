<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    public function success($data = null, string $message = 'success', int $code = 0): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function error(string $message = 'error', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'code' => $code,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    public function paginated(LengthAwarePaginator $paginator, string $message = 'success'): JsonResponse
    {
        return $this->success([
            'list' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_pages' => $paginator->lastPage(),
            ],
        ], $message);
    }

    public function paginatedWithStats(
        LengthAwarePaginator $paginator,
        array $stats,
        string $message = 'success'
    ): JsonResponse {
        return $this->success([
            'list' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_pages' => $paginator->lastPage(),
            ],
            'stats' => $stats,
        ], $message);
    }
}
