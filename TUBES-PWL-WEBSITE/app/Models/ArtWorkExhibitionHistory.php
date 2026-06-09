<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArtWorkExhibitionHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'art_work_exhibition_histories';
    protected $primaryKey = 'art_work_exhibition_history_id';

    protected $fillable = [
        'art_work_id',
        'exhibition_title',
        'venue_name',
        'city_name',
        'exhibition_date_display',
        'start_date',
        'end_date',
        'catalogue_reference',
        'exhibition_notes',
        'display_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function artWork()
    {
        return $this->belongsTo(ArtWork::class, 'art_work_id');
    }
}
