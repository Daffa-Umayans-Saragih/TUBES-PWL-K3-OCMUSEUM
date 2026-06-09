<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObjectType extends Model
{
    use HasFactory;

    protected $primaryKey = 'type_id';
    public $incrementing  = true;
    protected $keyType    = 'int';
    public $timestamps = false;

    protected $fillable = [
        'object_type_name',
    ];

    // timestamps mengikuti default Laravel (created_at, updated_at)

    public function artWorks(): HasMany
    {
        return $this->hasMany(ArtWork::class, 'type_id');
    }

    public function getNameAttribute()
    {
        return $this->object_type_name;
    }
}
