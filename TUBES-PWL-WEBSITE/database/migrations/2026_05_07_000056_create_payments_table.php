<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('payment_id');
            $table->unsignedInteger('order_id');
            $table->string('payment_method');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_status', ['Pending', 'Paid', 'Failed', 'Refunded']);
            $table->dateTime('paid_at')->nullable();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('order_id')->references('order_id')->on('orders');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
