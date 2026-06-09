<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Locale extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'locales';
    protected $primaryKey = 'locale_id';
    public $timestamps = false;

    protected $fillable = ['subregion_id', 'locale_name'];

    public function subregion()
    {
        return $this->belongsTo(Subregion::class, 'subregion_id');
    }

    public function loci()
    {
        return $this->hasMany(Locus::class, 'locale_id');
    }
}
