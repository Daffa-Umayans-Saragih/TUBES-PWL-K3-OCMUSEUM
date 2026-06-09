<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('art_work_exhibition_histories', function (Blueprint $table) {
            $table->increments('art_work_exhibition_history_id');
            $table->unsignedInteger('art_work_id');
            $table->text('exhibition_title');
            $table->string('venue_name', 255)->nullable();
            $table->string('city_name', 255)->nullable();
            $table->string('exhibition_date_display', 255)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('catalogue_reference', 255)->nullable();
            $table->text('exhibition_notes')->nullable();
            $table->integer('display_order')->default(1);
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('art_work_id')->references('art_work_id')->on('art_works');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('art_work_exhibition_histories');
    }
};
