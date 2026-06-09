<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medium extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mediums';
    protected $primaryKey = 'medium_id';
    public $timestamps = false;

    protected $fillable = [
        'medium_name',
    ];

    public function artWorks()
    {
        return $this->belongsToMany(ArtWork::class, 'art_work_mediums', 'medium_id', 'art_work_id')
                    ->withPivot('display_order');
    }
}
