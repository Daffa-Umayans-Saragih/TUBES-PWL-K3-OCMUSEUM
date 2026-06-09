<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $primaryKey = 'department_id';
    public $incrementing  = true;
    protected $keyType    = 'int';
    public $timestamps = false;

    protected $fillable = [
        'department_name',
        'department_image',
    ];

    // timestamps mengikuti default Laravel (created_at, updated_at)

    public function artWorks(): HasMany
    {
        return $this->hasMany(ArtWork::class, 'department_id');
    }

    public function getNameAttribute()
    {
        return $this->department_name;
    }
}
