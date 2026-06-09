<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_profile_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone_number',
        'address1',
        'address2',
        'postal_code_id',
    ];

    // timestamps diaktifkan agar created_at & updated_at otomatis

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function postalCode(): BelongsTo
    {
        return $this->belongsTo(PostalCode::class, 'postal_code_id', 'postal_code_id');
    }
}
