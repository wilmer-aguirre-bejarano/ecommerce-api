<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$request->user() || $request->user()-> role !== 'admin'){
            return response()->json([
                'message' => 'No tiene permisos para realizar esta accion.',
            ],403);//403 = Forbidden (autenticado pero sin permiso)
        }
        return $next($request);
    }
}
