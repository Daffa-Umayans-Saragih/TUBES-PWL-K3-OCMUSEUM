<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->increments('region_id');
            $table->unsignedInteger('country_id');
            $table->string('region_name');
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('country_id')->references('country_id')->on('countries');
            $table->unique(['country_id', 'region_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
