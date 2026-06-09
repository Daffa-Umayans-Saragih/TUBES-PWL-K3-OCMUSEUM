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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role_admin', ['cashier', 'admin', 'superadmin'])->nullable()->after('is_admin');
        });

        // Migrate existing data
        \Illuminate\Support\Facades\DB::table('users')
            ->where('is_admin', 1)
            ->update(['role_admin' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_admin');
        });
    }
};
