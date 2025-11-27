<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Formatter;
class TokenCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/payments/webhook')) {
            return $next($request);
        }
        if(!$request->bearerToken()) {
            return Formatter::apiResponse(403,"missing token");
        }
        if(!Auth::guard("sanctum")->check()) {
            return Formatter::apiResponse(403,"Invalid token");
        }
        return $next($request);
    }
}
