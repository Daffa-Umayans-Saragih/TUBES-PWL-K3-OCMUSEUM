<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->increments('city_id');
            $table->unsignedInteger('state_id');
            $table->string('city_name');
            $table->softDeletes();

            $table->foreign('state_id')->references('state_id')->on('states');
            $table->unique(['state_id', 'city_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
