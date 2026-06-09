<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketAvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ticket_availability_id,
            'ticket_type' => new TicketTypeResource($this->whenLoaded('ticketType')),
            'visit_schedule' => new VisitScheduleResource($this->whenLoaded('visitSchedule')),
        ];
    }
}
