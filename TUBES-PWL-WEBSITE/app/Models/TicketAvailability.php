<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAvailability extends Model
{
    use HasFactory;

    protected $table      = 'ticket_availability';
    protected $primaryKey = 'ticket_availability_id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'ticket_type_id',
        'visit_schedule_id',
        'capacity_limit',
    ];

    // timestamps diaktifkan agar created_at & updated_at otomatis

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function visitSchedule(): BelongsTo
    {
        return $this->belongsTo(VisitSchedule::class, 'visit_schedule_id');
    }
}
