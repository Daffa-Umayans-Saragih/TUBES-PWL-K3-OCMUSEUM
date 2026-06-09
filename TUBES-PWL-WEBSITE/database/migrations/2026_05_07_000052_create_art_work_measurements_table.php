<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_work_measurements', function (Blueprint $table) {
            $table->increments('art_work_measurement_id');
            $table->unsignedInteger('art_work_id');
            $table->string('measurement_type', 100)->nullable();
            $table->string('measurement_name', 100);
            $table->decimal('measurement_value', 12, 4);
            $table->string('measurement_unit', 50);
            $table->integer('display_order')->default(1);
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('art_work_id')->references('art_work_id')->on('art_works');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('art_work_measurements');
    }
};
