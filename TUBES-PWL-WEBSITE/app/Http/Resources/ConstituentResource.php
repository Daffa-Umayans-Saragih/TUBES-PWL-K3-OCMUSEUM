<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConstituentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->constituent_id,
            'name' => $this->display_name,
            'bio' => $this->display_bio,
            'alpha_sort' => $this->alpha_sort,
            'birth_year' => $this->birth_year,
            'death_year' => $this->death_year,
            'gender' => $this->gender,
            'ulan_url' => $this->ulan_url,
            'wikidata_url' => $this->wikidata_url,
        ];
    }
}
