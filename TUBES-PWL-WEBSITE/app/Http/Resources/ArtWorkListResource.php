<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtWorkListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->art_work_id,
            'met_object_id' => $this->met_object_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'object_date_display' => $this->object_date_display,
            'is_on_view' => $this->is_on_view,
            'is_highlight' => $this->is_highlight,
            'is_public_domain' => $this->is_public_domain,

            'department' => new DepartmentResource($this->whenLoaded('department')),
            'primary_image' => new ArtWorkImageResource($this->whenLoaded('images', function() {
                return $this->images->firstWhere('is_primary', true);
            })),
            
            'constituents' => ArtWorkConstituentResource::collection($this->whenLoaded('constituents')),
            'mediums' => MediumResource::collection($this->whenLoaded('mediums')),
        ];
    }
}
