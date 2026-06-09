<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postal_codes', function (Blueprint $table) {
            $table->increments('postal_code_id');
            $table->string('postal_code', 20);
            $table->string('postal_city', 100);
            $table->string('postal_state', 100);
            $table->string('postal_country', 100);
            $table->unique(
                ['postal_code', 'postal_city', 'postal_state', 'postal_country'],
                'uq_postal_codes_location'
            );
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postal_codes');
    }
};
