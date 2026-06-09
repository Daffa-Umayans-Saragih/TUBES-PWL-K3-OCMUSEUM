<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('constituent_nationalities', function (Blueprint $table) {
            $table->unsignedInteger('constituent_id');
            $table->unsignedInteger('nationality_id');
            $table->unique(['constituent_id', 'nationality_id']);

            $table->foreign('constituent_id')->references('constituent_id')->on('constituents');
            $table->foreign('nationality_id')->references('nationality_id')->on('nationalities');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('constituent_nationalities');
    }
};
