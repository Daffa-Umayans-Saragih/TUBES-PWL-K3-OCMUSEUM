<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->visit_schedule_id,
            'date' => $this->visit_date,
            'time' => $this->visit_time,
        ];
    }
}
