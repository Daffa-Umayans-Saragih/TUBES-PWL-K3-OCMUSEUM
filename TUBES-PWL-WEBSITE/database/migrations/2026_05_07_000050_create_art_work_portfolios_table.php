<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_work_portfolios', function (Blueprint $table) {
            $table->unsignedInteger('art_work_id');
            $table->unsignedInteger('portfolio_id');
            $table->unique(['art_work_id', 'portfolio_id']);

            $table->foreign('art_work_id')->references('art_work_id')->on('art_works');
            $table->foreign('portfolio_id')->references('portfolio_id')->on('portfolios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('art_work_portfolios');
    }
};
