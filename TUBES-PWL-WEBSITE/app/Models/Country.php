<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'countries';
    protected $primaryKey = 'country_id';
    public $timestamps = false;

    protected $fillable = ['country_name'];

    public function states()
    {
        return $this->hasMany(State::class, 'country_id');
    }

    public function regions()
    {
        return $this->hasMany(Region::class, 'country_id');
    }
}
