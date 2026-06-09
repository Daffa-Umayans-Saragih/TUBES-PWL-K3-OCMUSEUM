<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'regions';
    protected $primaryKey = 'region_id';
    public $timestamps = false;

    protected $fillable = ['country_id', 'region_name'];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function subregions()
    {
        return $this->hasMany(Subregion::class, 'region_id');
    }
}
