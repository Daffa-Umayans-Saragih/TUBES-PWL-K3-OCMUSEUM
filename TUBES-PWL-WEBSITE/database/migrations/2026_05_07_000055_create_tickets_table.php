<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('ticket_id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('ticket_availability_id');
            $table->string('qr_code')->unique();
            $table->enum('status', ['valid', 'used', 'cancelled']);
            $table->dateTime('used_at')->nullable();
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('order_id')->references('order_id')->on('orders');
            $table->foreign('ticket_availability_id')->references('ticket_availability_id')->on('ticket_availability');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
