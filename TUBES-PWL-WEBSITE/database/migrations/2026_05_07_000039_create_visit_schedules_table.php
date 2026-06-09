<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_schedules', function (Blueprint $table) {
            $table->increments('visit_schedule_id');
            $table->unsignedInteger('location_id');
            $table->date('visit_date');
            $table->integer('capacity_limit');
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('location_id')->references('location_id')->on('locations');
            $table->unique(['location_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_schedules');
    }
};
