<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected static function getExpiresIn(): int
    {
        $JWTAuth = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::class;

        return ($JWTAuth::factory()->getTTL() * 60 * 1000) + round(microtime(true) * 1000);
    }
}
