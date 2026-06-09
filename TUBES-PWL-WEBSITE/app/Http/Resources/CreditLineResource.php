<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->credit_line_id,
            'name' => $this->credit_line_text,
        ];
    }
}
