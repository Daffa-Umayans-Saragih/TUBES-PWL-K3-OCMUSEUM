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
        Schema::create('art_work_mediums', function (Blueprint $table) {
            $table->unsignedInteger('art_work_id');
            $table->unsignedInteger('medium_id');
            $table->integer('display_order')->default(1);
            $table->unique(['art_work_id', 'medium_id']);

            $table->foreign('art_work_id')->references('art_work_id')->on('art_works');
            $table->foreign('medium_id')->references('medium_id')->on('mediums');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('art_work_mediums');
    }
};
