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
        Schema::table('reviews', function (Blueprint $table) {
            // Moderation fields
            $table->enum('status', ['pending', 'approved', 'rejected', 'auto_approved'])->default('pending')->after('is_public');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null')->after('status');
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');
            $table->boolean('is_auto_approved')->default(false)->after('moderated_at');
            
            // Additional client fields
            $table->string('location')->nullable()->after('is_auto_approved');
            $table->boolean('verified')->default(false)->after('location');
            $table->boolean('is_featured')->default(false)->after('verified');
            $table->integer('helpful_count')->default(0)->after('is_featured');
            
            // Add indexes for performance
            $table->index('status');
            $table->index(['status', 'is_featured']);
            $table->index(['verified', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['verified', 'is_public']);
            $table->dropIndex(['status', 'is_featured']);
            $table->dropIndex(['status']);
            
            // Drop columns
            $table->dropColumn([
                'status',
                'moderated_by',
                'moderated_at',
                'is_auto_approved',
                'location',
                'verified',
                'is_featured',
                'helpful_count'
            ]);
        });
    }
};
