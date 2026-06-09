<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtWorkConstituent extends Pivot
{
    protected $table = 'art_work_constituents';

    public function role(): BelongsTo
    {
        return $this->belongsTo(ConstituentRole::class, 'role_id', 'role_id');
    }

    public function prefix(): BelongsTo
    {
        return $this->belongsTo(ConstituentPrefix::class, 'prefix_id', 'prefix_id');
    }

    public function suffix(): BelongsTo
    {
        return $this->belongsTo(ConstituentSuffix::class, 'suffix_id', 'suffix_id');
    }
}
