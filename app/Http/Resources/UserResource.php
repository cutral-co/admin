<?php

namespace App\Http\Resources;

use App\Http\Controllers\AppController;
use App\Models\App;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    public static function Auth()
    {
        $user = self::getUser();
        $token = self::getToken($user);

        $data = [
            'user' => $user,
            'token' => $token,
        ];

        return $data;
    }

    public static function AuthWithApps()
    {
        $user = self::getUser();
        $data = [
            'user' => $user,
            'roles' => $user->roles,
            'permissions' => $user->permissions,
            'apps' => self::getApps(),
            'token' => self::getToken($user),
        ];

        return $data;
    }

    private static function getUser()
    {
        $user = auth()->user();
        $user = User::where('id', $user->id)->with('person')->first();
        return $user;
    }

    private static function getApps()
    {
        $user = User::find(auth()->user()->id);

        $apps = App::where('enabled', 1)->get();
        if (!$user->hasRole('sudo')) {
            $apps = AppController::filterForPermission($apps, auth()->user());
        }

        return $apps;
    }

    private static function getToken($user)
    {
        return JWTAuth::claims([
            'id' => $user->id,
            'cuit' => $user->cuit,
        ])->fromUser($user);
    }
}
