<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArtWorkReference extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'art_work_references';
    protected $primaryKey = 'art_work_reference_id';

    protected $fillable = [
        'art_work_id',
        'reference_text',
        'display_order',
    ];

    public function artWork()
    {
        return $this->belongsTo(ArtWork::class, 'art_work_id');
    }
}
