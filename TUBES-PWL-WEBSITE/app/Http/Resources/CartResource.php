<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->cart_id,
            'expires_at' => $this->expires_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'guest' => new GuestResource($this->whenLoaded('guest')),
            'cart_groups' => CartGroupResource::collection($this->whenLoaded('cartGroups')),
        ];
    }
}
