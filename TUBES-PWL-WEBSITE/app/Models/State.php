<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'states';
    protected $primaryKey = 'state_id';
    public $timestamps = false;

    protected $fillable = ['country_id', 'state_name'];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function counties()
    {
        return $this->hasMany(County::class, 'state_id');
    }

    public function cities()
    {
        return $this->hasMany(City::class, 'state_id');
    }
}
