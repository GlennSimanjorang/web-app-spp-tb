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
            $userRole = $currentUser->role;
            if(!in_array($userRole, $roles ||is_null($userRole) )) {
                return Formatter::apiResponse(403, "You are not authorized to access this page.");
            }
            $request->mere(["user" => $currentUser]);
        }
        return $next($request);
    }
}
