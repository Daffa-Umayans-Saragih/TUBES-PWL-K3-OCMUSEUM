<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_code' => $this->order_code,
            'order_date' => $this->order_date,
            'total_amount' => $this->total_amount,
            'user' => new UserResource($this->whenLoaded('user')),
            'guest' => new GuestResource($this->whenLoaded('guest')),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
