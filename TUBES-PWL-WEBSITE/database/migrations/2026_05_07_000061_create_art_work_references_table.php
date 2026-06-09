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
        Schema::create('art_work_references', function (Blueprint $table) {
            $table->increments('art_work_reference_id');
            $table->unsignedInteger('art_work_id');
            $table->text('reference_text');
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
        Schema::dropIfExists('art_work_references');
    }
};
