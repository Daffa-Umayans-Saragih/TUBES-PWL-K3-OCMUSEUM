<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeographyType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'geography_types';
    protected $primaryKey = 'geography_type_id';
    public $timestamps = false;

    protected $fillable = ['geography_type_name'];
}
