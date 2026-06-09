<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtWorkMeasurementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->measurement_id,
            'element_name' => $this->element_name,
            'element_description' => $this->element_description,
            'measurements' => $this->measurements,
        ];
    }
}
