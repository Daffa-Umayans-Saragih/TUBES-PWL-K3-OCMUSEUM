<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynastyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->dynasty_id,
            'name' => $this->dynasty_name,
        ];
    }
}
