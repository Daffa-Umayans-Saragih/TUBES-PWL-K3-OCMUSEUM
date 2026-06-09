<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConstituentSuffixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->suffix_id,
            'name' => $this->suffix_name,
        ];
    }
}
