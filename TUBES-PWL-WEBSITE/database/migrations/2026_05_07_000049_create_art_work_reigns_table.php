<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_work_reigns', function (Blueprint $table) {
            $table->unsignedInteger('art_work_id');
            $table->unsignedInteger('reign_id');
            $table->unique(['art_work_id', 'reign_id']);

            $table->foreign('art_work_id')->references('art_work_id')->on('art_works');
            $table->foreign('reign_id')->references('reign_id')->on('reigns');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('art_work_reigns');
    }
};
