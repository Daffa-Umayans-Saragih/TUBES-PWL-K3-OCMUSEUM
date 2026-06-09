<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->cart_group_id,
            'cart_items' => CartItemResource::collection($this->whenLoaded('cartItems')),
        ];
    }
}
