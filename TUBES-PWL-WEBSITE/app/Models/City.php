<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cities';
    protected $primaryKey = 'city_id';
    public $timestamps = false;

    protected $fillable = ['state_id', 'city_name'];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
