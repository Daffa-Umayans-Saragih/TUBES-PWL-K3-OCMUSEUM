<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('constituent_prefixes', function (Blueprint $table) {
            $table->increments('prefix_id');
            $table->string('prefix_name')->unique();
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constituent_prefixes');
    }
};
