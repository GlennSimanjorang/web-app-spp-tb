<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Formatter;

class RolePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $currentUser = Auth::guard('sanctum')->user();

        if (!$currentUser) {
            return Formatter::apiResponse(401, "Unauthorized. Please login first.");
        }

        $userRole = $currentUser->role;

        if (is_null($userRole) || !in_array($userRole, $roles)) {
            return Formatter::apiResponse(403, "You are not authorized to access this page.");
        }

        $request->merge(["user" => $currentUser]);

        return $next($request);
    }
}
