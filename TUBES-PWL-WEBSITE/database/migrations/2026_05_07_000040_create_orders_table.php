<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id');
            $table->string('order_code', 50)->unique();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('guest_id')->nullable();
            $table->dateTime('order_date');
            $table->dateTime('expired_at')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->enum('order_status', [
                'pending_payment',
                'paid',
                'completed',
                'expired',
                'cancelled'
            ])->default('pending_payment');
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('user_id')->references('user_id')->on('users');
            $table->foreign('guest_id')->references('guest_id')->on('guests');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_user_guest_xor_check CHECK ((user_id IS NOT NULL AND guest_id IS NULL) OR (user_id IS NULL AND guest_id IS NOT NULL))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
