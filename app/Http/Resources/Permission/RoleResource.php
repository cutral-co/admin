<?php

namespace App\Http\Resources\Permission;

use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ];

        if (!$request->query('roles') == '1') {
            $array['permissions'] = PermissionResource::collection($this->permissions);
        }

        return $array;
    }
}
