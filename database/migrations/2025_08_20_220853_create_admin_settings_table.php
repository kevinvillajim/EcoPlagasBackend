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
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // business_hours, service_settings, etc.
            $table->json('value'); // JSON para almacenar la configuración
            $table->timestamps();
        });

        // Insert default settings
        $defaultSettings = [
            [
                'key' => 'business_hours',
                'value' => json_encode([
                    'monday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                    'tuesday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                    'wednesday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                    'thursday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                    'friday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                    'saturday' => ['open' => '08:00', 'close' => '14:00', 'isOpen' => true],
                    'sunday' => ['open' => '08:00', 'close' => '12:00', 'isOpen' => false]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'service_settings',
                'value' => json_encode([
                    'defaultServiceDuration' => 120,
                    'bufferTimeBetweenServices' => 30,
                    'advanceBookingDays' => 30,
                    'enableAdvanceBooking' => true,
                    'allowWeekendBooking' => true,
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'pricing_settings',
                'value' => json_encode([
                    'currency' => 'USD',
                    'baseServicePrice' => 75.00,
                    'includeTax' => true,
                    'taxRate' => 12,
                    'showPrices' => true,
                    'emergencyServiceSurcharge' => 50,
                    'weekendSurcharge' => 25,
                    'servicePrices' => [
                        'residential' => ['min' => 60, 'max' => 150, 'enabled' => true],
                        'commercial' => ['min' => 100, 'max' => 500, 'enabled' => true],
                        'industrial' => ['min' => 200, 'max' => 1000, 'enabled' => true],
                        'emergency' => ['min' => 80, 'max' => 300, 'enabled' => true]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'notification_settings',
                'value' => json_encode([
                    'emailNotifications' => true,
                    'clientReminders' => true,
                    'adminAlerts' => true,
                    'reminderHours' => 24,
                    'followUpDays' => 7
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('admin_settings')->insert($defaultSettings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
