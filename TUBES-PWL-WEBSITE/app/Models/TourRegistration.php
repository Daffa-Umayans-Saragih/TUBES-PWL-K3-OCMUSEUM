<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourRegistration extends Model
{
    protected $table = 'tour_registrations';
    protected $primaryKey = 'reg_id';
    protected $fillable = ['user_id', 'schedule_id'];
    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tourSchedule(): BelongsTo
    {
        return $this->belongsTo(TourSchedule::class, 'schedule_id', 'schedule_id');
    }
}
