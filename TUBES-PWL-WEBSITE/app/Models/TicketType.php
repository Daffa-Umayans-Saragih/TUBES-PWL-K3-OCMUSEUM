<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class TicketType extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'ticket_type_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'ticket_type_name',
        'base_price',
        'is_membership_discount_active',
        'membership_discount_type',
        'membership_discount_value',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_membership_discount_active' => 'boolean',
        'membership_discount_value' => 'decimal:2',
    ];

    // timestamps diaktifkan agar created_at & updated_at otomatis

    public function getEffectivePrice(?User $user = null): float
    {
        $price = (float) $this->base_price;

        if ($user && $user->premium_ended_at && $user->premium_ended_at->isFuture()) {
            if ($this->is_membership_discount_active) {
                if ($this->membership_discount_type === 'percentage') {
                    $price -= ($price * ((float) $this->membership_discount_value / 100));
                } elseif ($this->membership_discount_type === 'fixed') {
                    $price -= (float) $this->membership_discount_value;
                }
            }
        }

        return max(0, $price); // Prevent negative prices
    }

    public function ticketAvailabilities(): HasMany
    {
        return $this->hasMany(TicketAvailability::class, 'ticket_type_id', 'ticket_type_id');
    }

    public function getNameAttribute()
    {
        return $this->ticket_type_name;
    }
}
