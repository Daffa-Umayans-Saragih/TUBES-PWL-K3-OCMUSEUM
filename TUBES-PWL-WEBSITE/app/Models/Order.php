<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'order_code',
        'user_id',
        'guest_id',
        'order_date',
        'expired_at',
        'total_amount',
        'order_status',
        'order_type',
    ];

    protected $casts = [
        'order_date'   => 'datetime',
        'expired_at'   => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    // timestamps diaktifkan agar created_at & updated_at otomatis

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id')->latestOfMany('payment_id');
    }

    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class, 'order_id');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
}
