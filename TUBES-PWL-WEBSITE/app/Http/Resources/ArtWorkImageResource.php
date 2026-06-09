<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtWorkImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->image_id,
            'url' => $this->image_url,
            'is_primary' => $this->is_primary,
            'display_order' => $this->display_order,
        ];
    }
}
