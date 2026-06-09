<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('constituents', function (Blueprint $table) {
            $table->increments('constituent_id');
            $table->integer('met_constituent_id')->unique()->nullable();
            $table->string('display_name');
            $table->text('display_bio')->nullable();
            $table->string('alpha_sort')->nullable();
            $table->integer('birth_year')->nullable();
            $table->integer('death_year')->nullable();
            $table->string('birth_date_display')->nullable();
            $table->string('death_date_display')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('death_place')->nullable();
            $table->string('gender', 50)->nullable();
            $table->text('ulan_url')->nullable();
            $table->text('wikidata_url')->nullable();
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('constituents');
    }
};
