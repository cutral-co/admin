<?php

namespace App\Http\Middleware;

use Closure;
use Exception;

class JwtMiddleware extends \PHPOpenSourceSaver\JWTAuth\Http\Middleware\BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException) {
                return sendResponse(null, 'Llave invalida', 450);
            }
            if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException) {
                return sendResponse(null, 'Llave expiro', 450);
            }

            return sendResponse(null, 'Llave sin autorizacion', 450);

        }

        return $next($request);
    }
}
