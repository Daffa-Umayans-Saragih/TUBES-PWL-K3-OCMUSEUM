<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConstituentSuffix extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'constituent_suffixes';
    protected $primaryKey = 'suffix_id';

    protected $fillable = [
        'suffix_name',
    ];

    public function getNameAttribute()
    {
        return $this->suffix_name;
    }
}
