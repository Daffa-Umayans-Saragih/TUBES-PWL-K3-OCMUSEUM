<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtWorkGeographyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->geography_id,
            'geography_type' => new GeographyTypeResource($this->whenLoaded('geographyType')),
            'country' => new CountryResource($this->whenLoaded('country')),
            'state' => new StateResource($this->whenLoaded('state')),
            'county' => new CountyResource($this->whenLoaded('county')),
            'city' => new CityResource($this->whenLoaded('city')),
            'region' => new RegionResource($this->whenLoaded('region')),
            'subregion' => new SubregionResource($this->whenLoaded('subregion')),
            'locale' => new LocaleResource($this->whenLoaded('locale')),
            'locus' => new LocusResource($this->whenLoaded('locus')),
            'excavation' => new ExcavationResource($this->whenLoaded('excavation')),
            'river' => new RiverResource($this->whenLoaded('river')),
        ];
    }
}
