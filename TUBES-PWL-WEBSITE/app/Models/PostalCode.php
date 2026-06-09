<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class PostalCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'postal_codes';
    protected $primaryKey = 'postal_code_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'postal_code',
        'postal_city',
        'postal_state',
        'postal_country',
    ];

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'postal_code_id');
    }
}
