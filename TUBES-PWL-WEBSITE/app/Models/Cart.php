<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $primaryKey = 'cart_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'user_id',
        'guest_id',
        'expires_at',
    ];

    // timestamps diaktifkan agar created_at & updated_at otomatis

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }

    public function cartGroups(): HasMany
    {
        return $this->hasMany(CartGroup::class, 'cart_id');
    }
}
