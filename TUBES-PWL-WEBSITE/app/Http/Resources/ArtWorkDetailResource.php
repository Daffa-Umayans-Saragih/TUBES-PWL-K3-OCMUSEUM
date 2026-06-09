<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtWorkDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->art_work_id,
            'met_object_id' => $this->met_object_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'gallery_number' => $this->gallery_number,
            'accession_number' => $this->accession_number,
            'accession_year' => $this->accession_year,
            'object_date_display' => $this->object_date_display,
            'object_begin_date' => $this->object_begin_date,
            'object_end_date' => $this->object_end_date,
            'dimensions_display' => $this->dimensions_display,
            'rights_and_reproduction' => $this->rights_and_reproduction,
            'metadata_date' => $this->metadata_date,
            'provenance' => $this->provenance,
            'is_on_view' => $this->is_on_view,
            'is_highlight' => $this->is_highlight,
            'is_public_domain' => $this->is_public_domain,
            'is_timeline_work' => $this->is_timeline_work,
            'link_resource' => $this->link_resource,
            'object_url' => $this->object_url,
            'object_wikidata_url' => $this->object_wikidata_url,

            'department' => new DepartmentResource($this->whenLoaded('department')),
            'object_type' => new ObjectTypeResource($this->whenLoaded('objectType')),
            'classification' => new ClassificationResource($this->whenLoaded('classification')),
            'credit_line' => new CreditLineResource($this->whenLoaded('creditLine')),
            
            // Plural relationships
            'images' => ArtWorkImageResource::collection($this->whenLoaded('images')),
            'measurements' => ArtWorkMeasurementResource::collection($this->whenLoaded('measurements')),
            'exhibition_histories' => ArtWorkExhibitionHistoryResource::collection($this->whenLoaded('exhibitionHistories')),
            'references' => ArtWorkReferenceResource::collection($this->whenLoaded('references')),
            
            // Taxonomies
            'materials' => MaterialResource::collection($this->whenLoaded('materials')),
            'mediums' => MediumResource::collection($this->whenLoaded('mediums')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'cultures' => CultureResource::collection($this->whenLoaded('cultures')),
            'periods' => PeriodResource::collection($this->whenLoaded('periods')),
            'dynasties' => DynastyResource::collection($this->whenLoaded('dynasties')),
            'reigns' => ReignResource::collection($this->whenLoaded('reigns')),
            'portfolios' => PortfolioResource::collection($this->whenLoaded('portfolios')),

            // Complex attachments
            'constituents' => ArtWorkConstituentResource::collection($this->whenLoaded('constituents')),
            'geographies' => ArtWorkGeographyResource::collection($this->whenLoaded('geographies')),
            
            // SIM Metadata
            'sims' => ArtWorkSimResource::collection($this->whenLoaded('artWorkSims')),
        ];
    }
}
