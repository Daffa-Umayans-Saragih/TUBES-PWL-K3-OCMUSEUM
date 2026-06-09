<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    protected $primaryKey = 'tour_id';
    protected $fillable = ['tour_name', 'duration'];
    public $timestamps = true;

    public function tourSchedules(): HasMany
    {
        return $this->hasMany(TourSchedule::class, 'tour_id', 'tour_id');
    }
}
