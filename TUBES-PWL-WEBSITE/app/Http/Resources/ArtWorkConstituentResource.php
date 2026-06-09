<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtWorkConstituentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $this refers to the Constituent model in a belongsToMany relationship
        return [
            'constituent' => new ConstituentResource($this),
            'role' => $this->whenPivotLoaded('art_work_constituents', function () {
                return $this->pivot->role ? new ConstituentRoleResource($this->pivot->role) : null;
            }),
            'prefix' => $this->whenPivotLoaded('art_work_constituents', function () {
                return $this->pivot->prefix ? new ConstituentPrefixResource($this->pivot->prefix) : null;
            }),
            'suffix' => $this->whenPivotLoaded('art_work_constituents', function () {
                return $this->pivot->suffix ? new ConstituentSuffixResource($this->pivot->suffix) : null;
            }),
            'display_order' => $this->whenPivotLoaded('art_work_constituents', function () {
                return $this->pivot->display_order;
            }),
        ];
    }
}
