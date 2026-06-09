<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_work_periods', function (Blueprint $table) {
            $table->unsignedInteger('art_work_id');
            $table->unsignedInteger('period_id');
            $table->unique(['art_work_id', 'period_id']);

            $table->foreign('art_work_id')->references('art_work_id')->on('art_works');
            $table->foreign('period_id')->references('period_id')->on('periods');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('art_work_periods');
    }
};
