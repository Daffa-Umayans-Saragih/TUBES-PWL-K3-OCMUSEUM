<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Membership extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = 'membership_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'order_id',
        'user_id',
        'recipient_email',
        'membership_status',
        'is_gift',
        'auto_renewal',
        'activation_token',
        'token_expires_at',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'is_gift'          => 'boolean',
        'auto_renewal'     => 'boolean',
        'token_expires_at' => 'datetime',
        'activated_at'     => 'datetime',
        'expires_at'       => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
