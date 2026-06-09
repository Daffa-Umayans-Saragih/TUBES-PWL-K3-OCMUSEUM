<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class River extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rivers';
    protected $primaryKey = 'river_id';
    public $timestamps = false;

    protected $fillable = ['river_name'];
}
