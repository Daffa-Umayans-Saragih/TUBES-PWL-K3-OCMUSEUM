<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtWorkSimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sim_type' => $this->sim_type,
            'sim_text' => $this->sim_text,
        ];
    }
}
