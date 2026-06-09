<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->increments('state_id');
            $table->unsignedInteger('country_id');
            $table->string('state_name');
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('country_id')->references('country_id')->on('countries');
            $table->unique(['country_id', 'state_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
