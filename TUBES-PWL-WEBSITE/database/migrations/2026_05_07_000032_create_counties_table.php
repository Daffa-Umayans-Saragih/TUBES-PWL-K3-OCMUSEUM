<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counties', function (Blueprint $table) {
            $table->increments('county_id');
            $table->unsignedInteger('state_id');
            $table->string('county_name');
            $table->softDeletes();

            $table->foreign('state_id')->references('state_id')->on('states');
            $table->unique(['state_id', 'county_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counties');
    }
};
