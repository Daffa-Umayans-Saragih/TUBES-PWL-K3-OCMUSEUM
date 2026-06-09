<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Period extends Model
{
    protected $primaryKey = 'period_id';
    public $timestamps = false;
    protected $fillable = ['period_name'];

    public function artWorks(): BelongsToMany
    {
        return $this->belongsToMany(ArtWork::class, 'art_work_periods', 'period_id', 'art_work_id');
    }
}
