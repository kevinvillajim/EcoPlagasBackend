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
        Schema::table('gallery', function (Blueprint $table) {
            // Add video_url column to support videos
            $table->text('video_url')->nullable()->after('image_url');
            
            // Add media_type column to differentiate between image and video
            $table->enum('media_type', ['image', 'video'])->default('image')->after('video_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery', function (Blueprint $table) {
            $table->dropColumn('video_url');
            $table->dropColumn('media_type');
        });
    }
};