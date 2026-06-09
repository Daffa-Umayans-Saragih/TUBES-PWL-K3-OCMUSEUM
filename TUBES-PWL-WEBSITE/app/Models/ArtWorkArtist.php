<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtWorkArtist extends Model
{
    use HasFactory;

    protected $table = 'art_work_artists';

    protected $fillable = [
        'art_work_id',
        'artist_id',
    ];

    public $timestamps    = false;
    public $incrementing  = false;
    protected $primaryKey = null;

    public function artWork(): BelongsTo
    {
        return $this->belongsTo(ArtWork::class, 'art_work_id');
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }
}
