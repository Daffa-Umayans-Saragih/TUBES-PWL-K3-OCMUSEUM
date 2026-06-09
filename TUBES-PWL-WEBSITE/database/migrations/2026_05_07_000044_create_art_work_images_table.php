<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_work_images', function (Blueprint $table) {
            $table->increments('image_id');
            $table->unsignedInteger('art_work_id');
            $table->text('image_url');
            $table->boolean('is_primary')->default(false);
            $table->integer('display_order')->default(1);
            $table->softDeletes();
            $table->timestamps(); // FINAL SCHEMA: created_at & updated_at

            $table->foreign('art_work_id')->references('art_work_id')->on('art_works');
        });

        if (!in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("
                CREATE UNIQUE INDEX art_work_images_one_primary_per_artwork
                ON art_work_images (art_work_id, is_primary)
                WHERE is_primary = 1
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('art_work_images');
    }
};
