<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->increments('membership_id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('recipient_email', 255)->nullable();
            $table->enum('membership_status', [
                'verification_pending',
                'gift_pending_claim',
                'active',
                'expired',
                'cancelled',
            ])->default('verification_pending');
            $table->boolean('is_gift')->default(false);
            $table->boolean('auto_renewal')->default(false);
            $table->string('activation_token', 255)->nullable()->unique();
            $table->dateTime('token_expires_at')->nullable();
            $table->dateTime('activated_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')
                ->references('order_id')
                ->on('orders')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
