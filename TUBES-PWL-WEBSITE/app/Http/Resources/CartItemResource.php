<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->cart_item_id,
            'quantity' => $this->quantity,
            'ticket_availability' => new TicketAvailabilityResource($this->whenLoaded('ticketAvailability')),
        ];
    }
}
