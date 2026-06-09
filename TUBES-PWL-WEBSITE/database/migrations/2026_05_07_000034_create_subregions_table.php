<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subregions', function (Blueprint $table) {
            $table->increments('subregion_id');
            $table->unsignedInteger('region_id');
            $table->string('subregion_name');
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('region_id')->references('region_id')->on('regions');
            $table->unique(['region_id', 'subregion_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subregions');
    }
};
