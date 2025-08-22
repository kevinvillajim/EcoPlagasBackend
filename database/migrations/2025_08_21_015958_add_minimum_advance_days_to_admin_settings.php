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
        // Update existing service_settings to include minimumAdvanceDays field
        DB::statement("
            UPDATE admin_settings 
            SET value = JSON_SET(
                value, 
                '$.minimumAdvanceDays', 
                1
            )
            WHERE `key` = 'service_settings'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove minimumAdvanceDays field from service_settings
        DB::statement("
            UPDATE admin_settings 
            SET value = JSON_REMOVE(value, '$.minimumAdvanceDays')
            WHERE `key` = 'service_settings'
        ");
    }
};
