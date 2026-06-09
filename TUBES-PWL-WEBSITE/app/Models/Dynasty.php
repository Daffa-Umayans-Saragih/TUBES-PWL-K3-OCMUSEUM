<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dynasty extends Model
{
    protected $primaryKey = 'dynasty_id';
    public $timestamps = false;
    protected $fillable = ['dynasty_name'];

    public function artWorks(): BelongsToMany
    {
        return $this->belongsToMany(ArtWork::class, 'art_work_dynasties', 'dynasty_id', 'art_work_id');
    }
}
