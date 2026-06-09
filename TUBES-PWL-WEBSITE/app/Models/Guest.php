<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    use HasFactory;

    protected $primaryKey = 'guest_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    // timestamps diaktifkan agar created_at & updated_at otomatis

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'session_token',
    ];

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'guest_id', 'guest_id');
    }

    /**
     * Get the guest's full name.
     */
    public function getNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'guest_id', 'guest_id');
    }
}
