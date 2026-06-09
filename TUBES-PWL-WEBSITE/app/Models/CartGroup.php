<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartGroup extends Model
{
    use HasFactory;

    protected $primaryKey = 'cart_group_id';
    public $incrementing  = true;
    protected $keyType    = 'int';
    public $timestamps    = true;

    protected $fillable = [
        'cart_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_group_id');
    }
}
