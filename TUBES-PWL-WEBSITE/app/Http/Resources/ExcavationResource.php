<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExcavationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->excavation_id,
            'name' => $this->excavation_name,
        ];
    }
}
