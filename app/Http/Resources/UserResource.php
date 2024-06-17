<?php

namespace App\Http\Resources;

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

    public static function AuthWithToken($user)
    {
        $token = JWTAuth::claims([
            'id' => $user->id,
            'cuit' => $user->cuit,
            'a' => [
                'a',
                'b'
            ]
        ])
            ->fromUser($user);

        $data = [
            'user' => $user,
            'token' => $token,
        ];
        unset($data['user']->person->barrio_municipal);


        return $data;
    }
}
