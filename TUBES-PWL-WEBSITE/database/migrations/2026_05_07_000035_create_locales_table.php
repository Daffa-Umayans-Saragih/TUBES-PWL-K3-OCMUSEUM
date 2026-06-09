<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->increments('locale_id');
            $table->unsignedInteger('subregion_id');
            $table->string('locale_name');
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('subregion_id')->references('subregion_id')->on('subregions');
            $table->unique(['subregion_id', 'locale_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locales');
    }
};
