<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'postal_code' => new PostalCodeResource($this->whenLoaded('postalCode')),
        ];
    }
}
