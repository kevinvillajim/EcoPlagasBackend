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
            // Remove order_index column
            $table->dropColumn('order_index');
            
            // Add featured as direct boolean column
            $table->boolean('featured')->default(false)->after('is_active');
            
            // Remove metadata column as we'll use direct featured
            $table->dropColumn('metadata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery', function (Blueprint $table) {
            $table->integer('order_index')->default(0);
            $table->dropColumn('featured');
            $table->json('metadata')->nullable();
        });
    }
};
