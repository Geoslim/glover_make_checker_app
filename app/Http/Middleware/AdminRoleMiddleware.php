<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class AdminRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $adminRole
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next, string $adminRole)
    {
        if ($request->user()->role->slug === $adminRole) {
            return $next($request);
        }

        return response()->json([
            "success" => false,
            "message" => 'UNAUTHORIZED, You do not have the required access for this action.',
        ], Response::HTTP_FORBIDDEN);
    }
}
