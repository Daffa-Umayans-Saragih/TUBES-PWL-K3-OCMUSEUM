<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtWorkMaterial extends Model
{
    use HasFactory;

    protected $table = 'art_work_materials';

    protected $fillable = [
        'art_work_id',
        'material_id',
    ];

    public $timestamps    = false;
    public $incrementing  = false;
    protected $primaryKey = null;

    public function artWork(): BelongsTo
    {
        return $this->belongsTo(ArtWork::class, 'art_work_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
