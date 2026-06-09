<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artist extends Model
{
    use HasFactory;

    protected $primaryKey = 'artist_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'name',
        'nationality',
    ];

    public $timestamps = false;

    public function artWorks(): BelongsToMany
    {
        return $this->belongsToMany(ArtWork::class, 'art_work_artists', 'artist_id', 'art_work_id');
    }
}
