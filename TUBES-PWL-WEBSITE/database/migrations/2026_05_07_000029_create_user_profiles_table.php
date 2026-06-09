<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->increments('user_profile_id');
            $table->unsignedInteger('user_id')->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone_number', 20)->nullable();
            $table->string('address1', 255);
            $table->string('address2', 255)->nullable();
            $table->unsignedInteger('postal_code_id');
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('user_id')->references('user_id')->on('users');
            $table->foreign('postal_code_id')->references('postal_code_id')->on('postal_codes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
