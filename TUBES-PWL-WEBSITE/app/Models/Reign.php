<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reign extends Model
{
    protected $primaryKey = 'reign_id';
    public $timestamps = false;
    protected $fillable = ['reign_name'];

    public function artWorks(): BelongsToMany
    {
        return $this->belongsToMany(ArtWork::class, 'art_work_reigns', 'reign_id', 'art_work_id');
    }
}
