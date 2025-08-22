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
        // Add showPrices and servicePrices fields to pricing_settings
        DB::statement("
            UPDATE admin_settings 
            SET value = JSON_SET(
                value, 
                '$.showPrices', 
                true,
                '$.servicePrices',
                JSON_OBJECT(
                    'residential', JSON_OBJECT('min', 60, 'max', 150, 'enabled', true),
                    'commercial', JSON_OBJECT('min', 100, 'max', 500, 'enabled', true),
                    'industrial', JSON_OBJECT('min', 200, 'max', 1000, 'enabled', true),
                    'emergency', JSON_OBJECT('min', 80, 'max', 300, 'enabled', true)
                )
            )
            WHERE `key` = 'pricing_settings'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove showPrices and servicePrices fields
        DB::statement("
            UPDATE admin_settings 
            SET value = JSON_REMOVE(value, '$.showPrices', '$.servicePrices')
            WHERE `key` = 'pricing_settings'
        ");
    }
};
