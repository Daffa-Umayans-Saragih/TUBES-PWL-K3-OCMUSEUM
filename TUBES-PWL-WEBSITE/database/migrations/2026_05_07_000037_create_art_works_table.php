<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_works', function (Blueprint $table) {
            $table->increments('art_work_id');
            $table->integer('met_object_id')->unique();
            $table->string('accession_number')->unique();
            $table->integer('accession_year')->nullable();
            $table->string('title');
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('gallery_number', 100)->nullable();
            $table->boolean('is_on_view')->default(false);
            $table->boolean('is_highlight')->default(false);
            $table->boolean('is_public_domain')->default(false);
            $table->boolean('is_timeline_work')->default(false);
            $table->string('object_date_display')->nullable();
            $table->integer('object_begin_date')->nullable();
            $table->integer('object_end_date')->nullable();
            $table->text('dimensions_display')->nullable();
            $table->text('rights_and_reproduction')->nullable();
            $table->dateTime('metadata_date')->nullable();
            $table->unsignedInteger('repository_id');
            $table->text('link_resource')->nullable();
            $table->text('object_url')->nullable();
            $table->text('object_wikidata_url')->nullable();
            $table->text('provenance')->nullable();
            $table->unsignedInteger('department_id');
            $table->unsignedInteger('credit_line_id')->nullable();
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('classification_id')->nullable();
            $table->unsignedInteger('location_id');
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('repository_id')->references('repository_id')->on('repositories');
            $table->foreign('department_id')->references('department_id')->on('departments');
            $table->foreign('credit_line_id')->references('credit_line_id')->on('credit_lines');
            $table->foreign('type_id')->references('type_id')->on('object_types');
            $table->foreign('classification_id')->references('classification_id')->on('classifications');
            $table->foreign('location_id')->references('location_id')->on('locations');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('art_works');
    }
};
