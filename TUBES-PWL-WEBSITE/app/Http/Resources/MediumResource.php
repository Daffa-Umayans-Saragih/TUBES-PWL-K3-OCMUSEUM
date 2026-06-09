<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->medium_id,
            'name' => $this->medium_name,
        ];
    }
}
