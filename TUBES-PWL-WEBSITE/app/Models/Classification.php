<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classification extends Model
{
    use HasFactory;

    protected $primaryKey = 'classification_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'classification_name',
    ];

    // timestamps mengikuti default Laravel (created_at, updated_at)

    public function artWorks(): HasMany
    {
        return $this->hasMany(ArtWork::class, 'classification_id');
    }

    public function getNameAttribute()
    {
        return $this->classification_name;
    }
}
