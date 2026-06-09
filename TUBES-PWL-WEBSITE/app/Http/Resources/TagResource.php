<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->tag_id,
            'name' => $this->tag_term,
            'wiki_url' => $this->tag_wikidata_url,
            'aat_url' => $this->tag_aat_url,
        ];
    }
}
