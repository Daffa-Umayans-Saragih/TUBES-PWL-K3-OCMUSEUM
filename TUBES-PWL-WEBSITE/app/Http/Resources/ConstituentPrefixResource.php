<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConstituentPrefixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->prefix_id,
            'name' => $this->prefix_name,
        ];
    }
}
