<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CultureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->culture_id,
            'name' => $this->culture_name,
        ];
    }
}
