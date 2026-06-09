<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->locale_id,
            'name' => $this->locale_name,
        ];
    }
}
