<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConstituentRoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->role_id,
            'name' => $this->role_name,
        ];
    }
}
