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
            if (!Schema::hasColumn('gallery', 'video_url')) {
                $table->string('video_url')->nullable()->after('image_url');
            }
            if (!Schema::hasColumn('gallery', 'media_type')) {
                $table->string('media_type')->default('image')->after('video_url');
            }
            if (!Schema::hasColumn('gallery', 'featured')) {
                $table->boolean('featured')->default(false)->after('is_active');
            }
        });
        
        // Add indexes in a separate call to avoid conflicts
        try {
            Schema::table('gallery', function (Blueprint $table) {
                $table->index(['featured', 'is_active']);
                $table->index('media_type');
            });
        } catch (\Exception $e) {
            // Indexes might already exist, ignore errors
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery', function (Blueprint $table) {
            $table->dropIndex(['featured', 'is_active']);
            $table->dropIndex(['media_type']);
            $table->dropColumn(['video_url', 'media_type', 'featured']);
        });
    }
};
