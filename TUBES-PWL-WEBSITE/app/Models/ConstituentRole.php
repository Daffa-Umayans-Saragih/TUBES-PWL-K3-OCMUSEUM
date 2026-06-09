<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConstituentRole extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'constituent_roles';
    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_name',
    ];

    public function getNameAttribute()
    {
        return $this->role_name;
    }
}
