<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_work_constituents', function (Blueprint $table) {
            $table->increments('art_work_constituent_id');
            $table->unsignedInteger('art_work_id');
            $table->unsignedInteger('constituent_id');
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('prefix_id')->nullable();
            $table->unsignedInteger('suffix_id')->nullable();
            $table->integer('display_order')->default(1);
            $table->unique(['art_work_id', 'constituent_id', 'role_id']);
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('art_work_id')->references('art_work_id')->on('art_works');
            $table->foreign('constituent_id')->references('constituent_id')->on('constituents');
            $table->foreign('role_id')->references('role_id')->on('constituent_roles');
            $table->foreign('prefix_id')->references('prefix_id')->on('constituent_prefixes');
            $table->foreign('suffix_id')->references('suffix_id')->on('constituent_suffixes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('art_work_constituents');
    }
};
