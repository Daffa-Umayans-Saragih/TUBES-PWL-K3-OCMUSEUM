<?php

use Illuminate\Database\Migrations\Migration;

// DEPRECATED: This table has been replaced by cart_items and tickets per final schema.
// This migration is retained as a no-op stub to preserve migration history ordering.
return new class extends Migration
{
    public function up(): void
    {
        // No-op: order_details is deprecated. Commerce flow uses cart_items and tickets.
    }

    public function down(): void
    {
        // No-op.
    }
};
