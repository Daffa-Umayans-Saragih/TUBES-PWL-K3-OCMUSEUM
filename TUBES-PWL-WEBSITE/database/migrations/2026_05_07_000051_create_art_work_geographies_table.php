<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_work_geographies', function (Blueprint $table) {
            $table->increments('art_work_geography_id');
            $table->unsignedInteger('art_work_id');
            $table->unsignedInteger('geography_type_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('state_id')->nullable();
            $table->unsignedInteger('county_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedInteger('subregion_id')->nullable();
            $table->unsignedInteger('locale_id')->nullable();
            $table->unsignedInteger('locus_id')->nullable();
            $table->unsignedInteger('excavation_id')->nullable();
            $table->unsignedInteger('river_id')->nullable();
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('art_work_id')->references('art_work_id')->on('art_works');
            $table->foreign('geography_type_id')->references('geography_type_id')->on('geography_types');
            $table->foreign('country_id')->references('country_id')->on('countries');
            $table->foreign('state_id')->references('state_id')->on('states');
            $table->foreign('county_id')->references('county_id')->on('counties');
            $table->foreign('city_id')->references('city_id')->on('cities');
            $table->foreign('region_id')->references('region_id')->on('regions');
            $table->foreign('subregion_id')->references('subregion_id')->on('subregions');
            $table->foreign('locale_id')->references('locale_id')->on('locales');
            $table->foreign('locus_id')->references('locus_id')->on('loci');
            $table->foreign('excavation_id')->references('excavation_id')->on('excavations');
            $table->foreign('river_id')->references('river_id')->on('rivers');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('art_work_geographies');
    }
};
