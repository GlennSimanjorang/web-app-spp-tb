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
        // 🛑 Ganti Formatter::apiResponse dengan response()
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. Please login first.',
            'data' => null
        ], 401); 
    }

    $userRole = $currentUser->role;
    // 🛑 Tambahkan strtolower untuk memastikan perbandingan Case-Insensitive
    $rolesAllowed = array_map('strtolower', $roles); // Agar perbandingan toleran

    if (is_null($userRole) || !in_array(strtolower($userRole), $rolesAllowed)) {
        // 🛑 Ganti Formatter::apiResponse dengan response()
        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to access this page.',
            'data' => null
        ], 403); 
    }

    $request->merge(["user" => $currentUser]);

    return $next($request);
}
}
