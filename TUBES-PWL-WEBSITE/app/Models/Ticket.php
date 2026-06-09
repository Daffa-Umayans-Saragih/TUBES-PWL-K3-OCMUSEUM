<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $primaryKey = 'ticket_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'order_id',
        'ticket_availability_id',
        'qr_code',
        'status',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    // timestamps diaktifkan agar created_at & updated_at otomatis

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function ticketAvailability(): BelongsTo
    {
        return $this->belongsTo(TicketAvailability::class, 'ticket_availability_id');
    }
}
