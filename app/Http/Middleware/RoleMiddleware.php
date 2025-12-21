<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Allowed roles (comma-separated in route definition)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Convert string roles to UserRole enum
        $allowedRoles = [];
        foreach ($roles as $role) {
            try {
                $allowedRoles[] = UserRole::from($role);
            } catch (\ValueError $e) {
                // Invalid role, skip
                continue;
            }
        }

        if (empty($allowedRoles)) {
            // No valid roles specified, deny access
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        if ($request->user()->hasAnyRole($allowedRoles)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Access denied. You do not have the required permissions.',
        ], 403);
    }
}
