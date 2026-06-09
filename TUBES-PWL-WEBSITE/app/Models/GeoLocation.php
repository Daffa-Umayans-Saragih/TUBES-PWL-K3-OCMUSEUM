<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoLocation extends Model
{
    use HasFactory;

    protected $primaryKey = 'geo_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

    public function artWorks(): HasMany
    {
        return $this->hasMany(ArtWork::class, 'geo_id');
    }
}
