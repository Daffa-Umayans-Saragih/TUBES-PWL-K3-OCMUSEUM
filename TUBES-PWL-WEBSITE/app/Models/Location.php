<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $primaryKey = 'location_id';
    public $incrementing  = true;
    protected $keyType    = 'int';
    public $timestamps = false;

    protected $fillable = [
        'location_name',
        'address',
        'capacity_limit',
    ];

    // timestamps mengikuti default Laravel (created_at, updated_at)

    public function artWorks(): HasMany
    {
        return $this->hasMany(ArtWork::class, 'location_id');
    }

    public function visitSchedules(): HasMany
    {
        return $this->hasMany(VisitSchedule::class, 'location_id');
    }

    public function getNameAttribute()
    {
        return $this->location_name;
    }
}
