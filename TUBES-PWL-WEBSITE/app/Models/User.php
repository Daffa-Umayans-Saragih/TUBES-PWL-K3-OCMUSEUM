<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'email',
        'password',
        'is_admin',
        'role_admin',
        'premium_started_at',
        'premium_ended_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password'           => 'hashed',
            'is_admin'           => 'boolean',
            'premium_started_at' => 'datetime',
            'premium_ended_at'   => 'datetime',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'user_id');
    }

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        if ($this->profile) {
            return trim(($this->profile->first_name ?? '') . ' ' . ($this->profile->last_name ?? ''));
        }
        return 'User';
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'user_id', 'user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'user_id', 'user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id', 'user_id');
    }

    public function getRememberTokenName()
    {
        return null;
    }

    public function hasRole(array $roles): bool
    {
        return in_array($this->role_admin, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_admin === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role_admin === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role_admin === 'cashier';
    }
}
