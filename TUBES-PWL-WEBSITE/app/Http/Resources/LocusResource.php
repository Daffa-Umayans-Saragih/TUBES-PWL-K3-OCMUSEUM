<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->locus_id,
            'name' => $this->locus_name,
        ];
    }
}
