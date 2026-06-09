<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArtWork extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'art_work_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'met_object_id',
        'title',
        'slug',
        'description',
        'gallery_number',
        'accession_number',
        'accession_year',
        'object_date_display',
        'object_begin_date',
        'object_end_date',
        'dimensions_display',
        'rights_and_reproduction',
        'metadata_date',
        'is_on_view',
        'is_highlight',
        'is_public_domain',
        'is_timeline_work',
        'department_id',
        'type_id',
        'location_id',
        'repository_id',
        'classification_id',
        'link_resource',
        'object_url',
        'object_wikidata_url',
        'provenance',
        'credit_line_id',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function objectType(): BelongsTo
    {
        return $this->belongsTo(ObjectType::class, 'type_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class, 'repository_id');
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class, 'classification_id');
    }

    public function creditLine(): BelongsTo
    {
        return $this->belongsTo(CreditLine::class, 'credit_line_id');
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'art_work_materials', 'art_work_id', 'material_id');
    }

    public function mediums(): BelongsToMany
    {
        return $this->belongsToMany(Medium::class, 'art_work_mediums', 'art_work_id', 'medium_id')->withPivot('display_order');
    }

    public function constituents(): BelongsToMany
    {
        return $this->belongsToMany(Constituent::class, 'art_work_constituents', 'art_work_id', 'constituent_id')
            ->using(ArtWorkConstituent::class)
            ->withPivot(['role_id', 'prefix_id', 'suffix_id', 'display_order'])
            ->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'art_work_tags', 'art_work_id', 'tag_id');
    }

    public function cultures(): BelongsToMany
    {
        return $this->belongsToMany(Culture::class, 'art_work_cultures', 'art_work_id', 'culture_id');
    }

    public function periods(): BelongsToMany
    {
        return $this->belongsToMany(Period::class, 'art_work_periods', 'art_work_id', 'period_id');
    }

    public function dynasties(): BelongsToMany
    {
        return $this->belongsToMany(Dynasty::class, 'art_work_dynasties', 'art_work_id', 'dynasty_id');
    }

    public function reigns(): BelongsToMany
    {
        return $this->belongsToMany(Reign::class, 'art_work_reigns', 'art_work_id', 'reign_id');
    }

    public function portfolios(): BelongsToMany
    {
        return $this->belongsToMany(Portfolio::class, 'art_work_portfolios', 'art_work_id', 'portfolio_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ArtWorkImage::class, 'art_work_id');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(ArtWorkMeasurement::class, 'art_work_id');
    }

    public function exhibitionHistories(): HasMany
    {
        return $this->hasMany(ArtWorkExhibitionHistory::class, 'art_work_id');
    }

    public function references(): HasMany
    {
        return $this->hasMany(ArtWorkReference::class, 'art_work_id');
    }

    public function geographies(): HasMany
    {
        return $this->hasMany(ArtWorkGeography::class, 'art_work_id');
    }

    public function getNameAttribute()
    {
        return $this->title;
    }

    public function getYearStartAttribute()
    {
        return $this->object_begin_date;
    }

    public function getYearEndAttribute()
    {
        return $this->object_end_date;
    }

    public function getObjectNumberAttribute()
    {
        return $this->accession_number;
    }

    public function getImageUrlAttribute()
    {
        $images = $this->images; // Use loaded collection
        if ($images->isEmpty()) {
            return null;
        }

        $primary = $images->where('is_primary', true)->first();
        return $primary ? $primary->image_url : $images->first()->image_url;
    }

    public function getResolvedImageUrlAttribute()
    {
        $images = $this->images; // Use loaded collection
        if ($images->isEmpty()) {
            return null;
        }

        $primary = $images->where('is_primary', true)->first();
        return $primary ? $primary->resolved_url : $images->first()->resolved_url;
    }

    public function artWorkSims(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ArtWorkSim::class, 'art_work_id');
    }
}
