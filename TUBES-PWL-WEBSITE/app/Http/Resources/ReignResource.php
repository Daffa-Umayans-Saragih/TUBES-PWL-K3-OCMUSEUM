<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->reign_id,
            'name' => $this->reign_name,
        ];
    }
}
