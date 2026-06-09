<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_availability', function (Blueprint $table) {
            $table->increments('ticket_availability_id');
            $table->unsignedInteger('ticket_type_id');
            $table->unsignedInteger('visit_schedule_id');
            $table->unique(['ticket_type_id', 'visit_schedule_id']);
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('ticket_type_id')->references('ticket_type_id')->on('ticket_types');
            $table->foreign('visit_schedule_id')->references('visit_schedule_id')->on('visit_schedules');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_availability');
    }
};
