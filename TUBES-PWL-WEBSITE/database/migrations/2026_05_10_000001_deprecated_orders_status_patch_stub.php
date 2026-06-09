<?php

use Illuminate\Database\Migrations\Migration;

// DEPRECATED PATCH: orders.status column is not part of the final schema.
// Order state is determined by querying the payments and tickets tables.
// This migration is retained as a no-op stub to preserve migration history ordering.
return new class extends Migration
{
    public function up(): void
    {
        // No-op: orders.status is not in the final schema.
    }

    public function down(): void
    {
        // No-op.
    }
};
