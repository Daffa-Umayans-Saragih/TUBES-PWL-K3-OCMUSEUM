<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Material extends Model
{
    use HasFactory;

    protected $primaryKey = 'material_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'material_name',
    ];

    // timestamps mengikuti default Laravel (created_at, updated_at)

    public function artWorks(): BelongsToMany
    {
        return $this->belongsToMany(ArtWork::class, 'art_work_materials', 'material_id', 'art_work_id');
    }
}
