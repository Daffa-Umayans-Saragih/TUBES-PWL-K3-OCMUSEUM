<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('ticket_types')
            ->whereRaw('LOWER(ticket_type_name) = ?', ['disabilities'])
            ->exists();

        if (!$exists) {
            DB::table('ticket_types')->insert([
                'ticket_type_name' => 'Disabilities',
                'base_price' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('ticket_types')
            ->whereRaw('LOWER(ticket_type_name) = ?', ['disabilities'])
            ->delete();
    }
};
