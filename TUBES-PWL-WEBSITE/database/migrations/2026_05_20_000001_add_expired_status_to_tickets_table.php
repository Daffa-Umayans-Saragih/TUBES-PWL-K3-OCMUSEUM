<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds 'expired' to the tickets.status enum.
 *
 * Business context:
 *   A ticket becomes `expired` when its associated visit_schedule's visit_date
 *   passes without the ticket being scanned (status = 'used').
 *   This is distinct from `cancelled` (admin-initiated) and `used` (QR scanned).
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite does not support ALTER COLUMN on enums.
            // For SQLite (used in testing) we use a raw CHECK constraint workaround.
            // The column type stays as string; the enum() call effectively becomes a VARCHAR
            // with a CHECK constraint in SQLite. We drop the old constraint and add a new one.
            // Since SQLite doesn't support DROP CONSTRAINT, we recreate via table rebuild.
            // 
            // For tests, the simplest approach is to just use a string column and enforce
            // the constraint in application logic — which Laravel already does via ->enum().
            //
            // We temporarily disable foreign keys and rebuild:
            DB::statement('PRAGMA foreign_keys=OFF');

            DB::statement(<<<'SQL'
                CREATE TABLE tickets_new (
                    ticket_id              INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id               INTEGER NOT NULL,
                    ticket_availability_id INTEGER NOT NULL,
                    qr_code                VARCHAR NOT NULL UNIQUE,
                    status                 VARCHAR NOT NULL CHECK(status IN ('valid','used','cancelled','expired')),
                    used_at                DATETIME,
                    deleted_at             DATETIME,
                    created_at             DATETIME,
                    updated_at             DATETIME,
                    FOREIGN KEY (order_id) REFERENCES orders(order_id),
                    FOREIGN KEY (ticket_availability_id) REFERENCES ticket_availability(ticket_availability_id)
                )
            SQL);

            DB::statement('INSERT INTO tickets_new SELECT * FROM tickets');
            DB::statement('DROP TABLE tickets');
            DB::statement('ALTER TABLE tickets_new RENAME TO tickets');

            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            // MySQL / MariaDB: extend the ENUM
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('valid','used','cancelled','expired') NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'sqlite') {
            // Revert MySQL enum — tickets with status='expired' would need to be updated first
            DB::statement("UPDATE tickets SET status = 'cancelled' WHERE status = 'expired'");
            DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('valid','used','cancelled') NOT NULL");
        }
    }
};
