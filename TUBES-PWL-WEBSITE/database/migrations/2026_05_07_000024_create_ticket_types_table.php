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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->increments('ticket_type_id');
            $table->string('ticket_type_name', 100)->unique();
            $table->decimal('base_price', 15, 2);
            
            // Membership discount config
            $table->boolean('is_membership_discount_active')->default(false);
            $table->enum('membership_discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('membership_discount_value', 15, 2)->default(0);

            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
