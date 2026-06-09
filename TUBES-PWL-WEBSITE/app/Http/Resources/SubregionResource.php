<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubregionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->subregion_id,
            'name' => $this->subregion_name,
        ];
    }
}
