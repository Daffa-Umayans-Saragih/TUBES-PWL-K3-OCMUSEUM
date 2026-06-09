<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Locus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'loci';
    protected $primaryKey = 'locus_id';
    public $timestamps = false;

    protected $fillable = ['locale_id', 'locus_name'];

    public function locale()
    {
        return $this->belongsTo(Locale::class, 'locale_id');
    }
}
