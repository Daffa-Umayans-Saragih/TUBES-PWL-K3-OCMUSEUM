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
        Schema::create('art_work_sims', function (Blueprint $table) {
            $table->id('art_work_sim_id');
            $table->unsignedInteger('art_work_id');
            $table->foreign('art_work_id')->references('art_work_id')->on('art_works')->onDelete('cascade');
            $table->enum('sim_type', ['Signature', 'Inscription', 'Marking']);
            $table->text('sim_text');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('art_work_sims');
    }
};
