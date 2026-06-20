<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CheckAnyPermission
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function handle(Request $request, Closure $next, string ...$permissions): JsonResponse
    {
        try {
            $this->permissionService->ensureAnyPermission($permissions);
        } catch (\App\Exceptions\BusinessException $e) {
            return response()->json([
                'code' => $e->getErrorCode(),
                'message' => $e->getMessage(),
            ], $e->getErrorCode());
        }

        return $next($request);
    }
}
