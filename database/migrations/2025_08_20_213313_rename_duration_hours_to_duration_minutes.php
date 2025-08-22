<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_settings', function (Blueprint $table) {
            // Convert existing hours to minutes and rename column
            $table->renameColumn('duration_hours', 'duration_minutes');
        });
        
        // Update existing data: convert hours to minutes
        DB::table('service_settings')->update([
            'duration_minutes' => DB::raw('duration_minutes * 60')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_settings', function (Blueprint $table) {
            // Convert minutes back to hours and rename column
            $table->renameColumn('duration_minutes', 'duration_hours');
        });
        
        // Update existing data: convert minutes back to hours
        DB::table('service_settings')->update([
            'duration_hours' => DB::raw('duration_hours / 60')
        ]);
    }
};
