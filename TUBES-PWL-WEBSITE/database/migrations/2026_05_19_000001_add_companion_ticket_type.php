<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure Companion ticket type exists
        $companionId = DB::table('ticket_types')
            ->whereRaw('LOWER(ticket_type_name) = ?', ['companion'])
            ->value('ticket_type_id');

        if (!$companionId) {
            $companionId = DB::table('ticket_types')->insertGetId([
                'ticket_type_name' => 'Companion',
                'base_price' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Ensure all existing visit schedules have companion availability
        $schedules = DB::table('visit_schedules')->get();
        foreach ($schedules as $schedule) {
            $exists = DB::table('ticket_availability')
                ->where('visit_schedule_id', $schedule->visit_schedule_id)
                ->where('ticket_type_id', $companionId)
                ->exists();

            if (!$exists) {
                DB::table('ticket_availability')->insert([
                    'visit_schedule_id' => $schedule->visit_schedule_id,
                    'ticket_type_id' => $companionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $companionId = DB::table('ticket_types')
            ->whereRaw('LOWER(ticket_type_name) = ?', ['companion'])
            ->value('ticket_type_id');

        if ($companionId) {
            DB::table('ticket_availability')
                ->where('ticket_type_id', $companionId)
                ->delete();

            DB::table('ticket_types')
                ->where('ticket_type_id', $companionId)
                ->delete();
        }
    }
};
