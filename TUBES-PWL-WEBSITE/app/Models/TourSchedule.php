<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourSchedule extends Model
{
    protected $table = 'tour_schedules';
    protected $primaryKey = 'schedule_id';
    protected $fillable = [
        'tour_id',
        'location_id',
        'start_time',
        'max_participants',
        'current_slots'
    ];
    protected $casts = [
        'start_time' => 'datetime',
    ];
    public $timestamps = true;

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_id', 'tour_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    public function tourRegistrations(): HasMany
    {
        return $this->hasMany(TourRegistration::class, 'schedule_id', 'schedule_id');
    }
}
