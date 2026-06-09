<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subregion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subregions';
    protected $primaryKey = 'subregion_id';
    public $timestamps = false;

    protected $fillable = ['region_id', 'subregion_name'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function locales()
    {
        return $this->hasMany(Locale::class, 'subregion_id');
    }
}
