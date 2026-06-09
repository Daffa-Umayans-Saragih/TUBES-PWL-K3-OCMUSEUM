<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->river_id,
            'name' => $this->river_name,
        ];
    }
}
