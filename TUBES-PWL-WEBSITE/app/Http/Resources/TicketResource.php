<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ticket_id,
            'qr_code' => $this->qr_code,
            'status' => $this->status,
            'used_at' => $this->used_at,
            'ticket_availability' => new TicketAvailabilityResource($this->whenLoaded('ticketAvailability')),
        ];
    }
}
