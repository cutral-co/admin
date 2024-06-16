<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceUnavailable
{
    public $rutas_permitidas = ['/api/internal_login'];
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isDownForMaintenance() && !in_array($_SERVER['REQUEST_URI'], $this->rutas_permitidas)) {
            return sendResponse(null, 'Servicio no disponible', 503);
        }
        return $next($request);
    }
}
