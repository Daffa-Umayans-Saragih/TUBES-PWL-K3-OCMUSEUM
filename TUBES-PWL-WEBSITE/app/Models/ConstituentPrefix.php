<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConstituentPrefix extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'constituent_prefixes';
    protected $primaryKey = 'prefix_id';

    protected $fillable = [
        'prefix_name',
    ];

    public function getNameAttribute()
    {
        return $this->prefix_name;
    }
}
