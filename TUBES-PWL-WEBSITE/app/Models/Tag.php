<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tags';
    protected $primaryKey = 'tag_id';

    protected $fillable = [
        'tag_term',
        'aat_url',
        'wikidata_url',
    ];

    public function artWorks()
    {
        return $this->belongsToMany(ArtWork::class, 'art_work_tags', 'tag_id', 'art_work_id');
    }
}
