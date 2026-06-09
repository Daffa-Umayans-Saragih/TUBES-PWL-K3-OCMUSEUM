<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Excavation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'excavations';
    protected $primaryKey = 'excavation_id';
    public $timestamps = false;

    protected $fillable = ['excavation_name'];
}
