<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArtWorkGeography extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'art_work_geographies';
    protected $primaryKey = 'art_work_geography_id';

    protected $fillable = [
        'art_work_id',
        'geography_type_id',
        'country_id',
        'state_id',
        'county_id',
        'city_id',
        'region_id',
        'subregion_id',
        'locale_id',
        'locus_id',
        'excavation_id',
        'river_id',
    ];

    public function artWork()
    {
        return $this->belongsTo(ArtWork::class, 'art_work_id');
    }

    public function geographyType()
    {
        return $this->belongsTo(GeographyType::class, 'geography_type_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function county()
    {
        return $this->belongsTo(County::class, 'county_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function subregion()
    {
        return $this->belongsTo(Subregion::class, 'subregion_id');
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class, 'locale_id');
    }

    public function locus()
    {
        return $this->belongsTo(Locus::class, 'locus_id');
    }

    public function excavation()
    {
        return $this->belongsTo(Excavation::class, 'excavation_id');
    }

    public function river()
    {
        return $this->belongsTo(River::class, 'river_id');
    }
}
