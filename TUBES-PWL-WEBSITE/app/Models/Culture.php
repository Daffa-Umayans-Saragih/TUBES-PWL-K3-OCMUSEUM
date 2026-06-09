<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Culture extends Model
{
    use HasFactory;

    protected $table      = 'cultures';
    protected $primaryKey = 'culture_id';
    // timestamps mengikuti default Laravel (created_at, updated_at)

    protected $fillable = [
        'culture_name',
    ];

    public function artWorks(): BelongsToMany
    {
        return $this->belongsToMany(ArtWork::class, 'art_work_cultures', 'culture_id', 'art_work_id');
    }
}
