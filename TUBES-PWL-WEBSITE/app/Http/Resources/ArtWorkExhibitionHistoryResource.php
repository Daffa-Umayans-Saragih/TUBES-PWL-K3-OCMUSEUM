<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtWorkExhibitionHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->exhibition_history_id,
            'exhibition_text' => $this->exhibition_text,
        ];
    }
}
