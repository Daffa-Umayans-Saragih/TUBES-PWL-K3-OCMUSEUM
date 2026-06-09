<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_groups', function (Blueprint $table) {
            $table->increments('cart_group_id');
            $table->unsignedInteger('cart_id');
            $table->timestamp('created_at'); // FINAL SCHEMA: NOT NULL
            $table->timestamp('updated_at')->nullable();

            $table->foreign('cart_id')->references('cart_id')->on('carts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_groups');
    }
};
