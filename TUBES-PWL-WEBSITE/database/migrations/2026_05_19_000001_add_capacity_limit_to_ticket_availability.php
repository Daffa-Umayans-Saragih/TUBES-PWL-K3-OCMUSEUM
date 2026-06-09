<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_availability')) {
            Schema::table('ticket_availability', function (Blueprint $table) {
                if (!Schema::hasColumn('ticket_availability', 'capacity_limit')) {
                    $table->integer('capacity_limit')->nullable()->after('visit_schedule_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ticket_availability')) {
            Schema::table('ticket_availability', function (Blueprint $table) {
                if (Schema::hasColumn('ticket_availability', 'capacity_limit')) {
                    $table->dropColumn('capacity_limit');
                }
            });
        }
    }
};
