<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->increments('order_detail_id');

            $table->unsignedInteger('order_id');
            $table->unsignedInteger('ticket_id');

            $table->unsignedInteger('quantity');

            // Financial snapshot
            $table->decimal('original_price', 12, 2)->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);

            // Audit safety
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')
                ->references('order_id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->foreign('ticket_id')
                ->references('ticket_id')
                ->on('tickets')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
