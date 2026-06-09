<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loci', function (Blueprint $table) {
            $table->increments('locus_id');
            $table->unsignedInteger('locale_id');
            $table->string('locus_name');
            $table->softDeletes();

            $table->foreign('locale_id')->references('locale_id')->on('locales');
            $table->unique(['locale_id', 'locus_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loci');
    }
};
