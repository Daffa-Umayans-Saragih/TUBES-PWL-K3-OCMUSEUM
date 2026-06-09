<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->increments('guest_id');
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('session_token')->unique()->nullable();
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
