<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArtWorkMeasurement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'art_work_measurements';
    protected $primaryKey = 'art_work_measurement_id';

    protected $fillable = [
        'art_work_id',
        'measurement_type',
        'measurement_name',
        'measurement_value',
        'measurement_unit',
        'display_order',
    ];

    protected $casts = [
        'measurement_value' => 'decimal:4',
    ];

    public function artWork()
    {
        return $this->belongsTo(ArtWork::class, 'art_work_id');
    }
}
