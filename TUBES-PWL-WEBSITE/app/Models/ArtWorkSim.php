<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtWorkSim extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'art_work_sims';
    protected $primaryKey = 'art_work_sim_id';

    protected $fillable = [
        'art_work_id',
        'sim_type',
        'sim_text',
    ];

    public function artWork(): BelongsTo
    {
        return $this->belongsTo(ArtWork::class, 'art_work_id');
    }
}
