<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create essential admin settings
        $this->seedAdminSettings();
        
        // 2. Create service settings (the 4 essential service types)
        $this->seedServiceSettings();
        
        // 3. Create default admin user if none exists
        $this->seedDefaultAdminUser();
        
        // 4. Create default gallery categories
        // $this->seedGalleryCategories();
        
        // 5. Create default notification templates
        // $this->seedNotificationTemplates();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove seeded data
        DB::table('admin_settings')->whereIn('key', [
            'business_hours',
            'service_settings', 
            'pricing_settings',
            'notification_settings'
        ])->delete();
        
        DB::table('service_settings')->whereIn('service_type', [
            'residential', 'commercial', 'industrial', 'emergency'
        ])->delete();
        
        // Don't delete admin user or other data in down() for safety
    }

    private function seedAdminSettings(): void
    {
        $settings = [
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
                ])
            ],
            [
                'key' => 'service_settings',
                'value' => json_encode([
                    'defaultServiceDuration' => 120,
                    'bufferTimeBetweenServices' => 30,
                    'minimumAdvanceDays' => 1,
                    'enableAdvanceBooking' => true,
                    'allowWeekendBooking' => true
                ])
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
                ])
            ],
            [
                'key' => 'notification_settings',
                'value' => json_encode([
                    'emailNotifications' => true,
                    'clientReminders' => true,
                    'adminAlerts' => true,
                    'reminderHours' => 24,
                    'followUpDays' => 7
                ])
            ]
        ];

        foreach ($settings as $setting) {
            DB::table('admin_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }

    private function seedServiceSettings(): void
    {
        $services = [
            [
                'service_type' => 'residential',
                'duration_minutes' => 180,
                'min_price' => 60.00,
                'max_price' => 150.00,
                'description' => 'Control de Plagas Residencial - Tratamientos especializados para hogares y apartamentos',
                'is_active' => true
            ],
            [
                'service_type' => 'commercial',
                'duration_minutes' => 180,
                'min_price' => 100.00,
                'max_price' => 500.00,
                'description' => 'Control de Plagas Comercial - Soluciones integrales para empresas y locales comerciales',
                'is_active' => true
            ],
            [
                'service_type' => 'industrial',
                'duration_minutes' => 240,
                'min_price' => 200.00,
                'max_price' => 1000.00,
                'description' => 'Control de Plagas Industrial - Tratamientos especializados para plantas industriales',
                'is_active' => true
            ],
            [
                'service_type' => 'emergency',
                'duration_minutes' => 60,
                'min_price' => 80.00,
                'max_price' => 300.00,
                'description' => 'Servicio de Emergencia - Atención inmediata para infestaciones severas 24/7',
                'is_active' => true
            ]
        ];

        foreach ($services as $service) {
            DB::table('service_settings')->updateOrInsert(
                ['service_type' => $service['service_type']],
                array_merge($service, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }

    private function seedDefaultAdminUser(): void
    {
        // Only create admin if no admin users exist
        $adminExists = DB::table('users')
            ->where('role', 'admin')
            ->exists();

        if (!$adminExists) {
            DB::table('users')->insert([
                'name' => 'Kevin Villacreses',
                'email' => 'kevinvillajim@hotmail.com',
                'password' => Hash::make('Olvidon2@'),
                'role' => 'admin',
                'phone' => '593963368896',
                'city' => 'Quito',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            DB::table('users')->insert([
                'name' => 'Efraín Villacreses',
                'email' => 'efravillacrses@gmail.com',
                'password' => Hash::make('Olvidon1@'),
                'role' => 'admin',
                'phone' => '593995031066',
                'city' => 'Quito',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    // private function seedGalleryCategories(): void
    // {
    //     // Create some basic gallery items to showcase services
    //     // Based on actual table structure: id, title, description, image_url, video_url, media_type, category, is_active, featured
    //     $galleryItems = [
    //         [
    //             'title' => 'Control de Plagas Residencial',
    //             'description' => 'Tratamiento profesional en hogares',
    //             'image_url' => '/storage/gallery/residential-default.jpg',
    //             'video_url' => null,
    //             'media_type' => 'image',
    //             'category' => 'fumigacion_residencial',
    //             'is_active' => 1,
    //             'featured' => 1
    //         ],
    //         [
    //             'title' => 'Control de Plagas Comercial',
    //             'description' => 'Soluciones para empresas y negocios',
    //             'image_url' => '/storage/gallery/commercial-default.jpg',
    //             'video_url' => null,
    //             'media_type' => 'image',
    //             'category' => 'fumigacion_comercial',
    //             'is_active' => 1,
    //             'featured' => 1
    //         ],
    //         [
    //             'title' => 'Control de Plagas Industrial',
    //             'description' => 'Tratamientos especializados industriales',
    //             'image_url' => '/storage/gallery/industrial-default.jpg',
    //             'video_url' => null,
    //             'media_type' => 'image',
    //             'category' => 'fumigacion_industrial',
    //             'is_active' => 1,
    //             'featured' => 1
    //         ],
    //         [
    //             'title' => 'Servicio de Emergencia',
    //             'description' => 'Atención inmediata para infestaciones severas',
    //             'image_url' => '/storage/gallery/emergency-default.jpg',
    //             'video_url' => null,
    //             'media_type' => 'image',
    //             'category' => 'emergencia',
    //             'is_active' => 1,
    //             'featured' => 1
    //         ]
    //     ];

    //     foreach ($galleryItems as $item) {
    //         // Check if gallery table exists before inserting
    //         if (Schema::hasTable('gallery')) {
    //             DB::table('gallery')->updateOrInsert(
    //                 ['title' => $item['title']],
    //                 array_merge($item, [
    //                     'created_at' => now(),
    //                     'updated_at' => now()
    //                 ])
    //             );
    //         }
    //     }
    // }

    // private function seedNotificationTemplates(): void
    // {
    //     // Create default notification templates if notifications table exists
    //     if (Schema::hasTable('notifications')) {
    //         $templates = [
    //             [
    //                 'type' => 'service_scheduled',
    //                 'title' => 'Servicio Programado',
    //                 'message' => 'Su servicio ha sido programado exitosamente.',
    //                 'data' => json_encode(['template' => true])
    //             ],
    //             [
    //                 'type' => 'service_reminder',
    //                 'title' => 'Recordatorio de Servicio',
    //                 'message' => 'Recordatorio: Su servicio está programado para mañana.',
    //                 'data' => json_encode(['template' => true])
    //             ],
    //             [
    //                 'type' => 'service_completed',
    //                 'title' => 'Servicio Completado',
    //                 'message' => 'Su servicio ha sido completado satisfactoriamente.',
    //                 'data' => json_encode(['template' => true])
    //             ]
    //         ];

    //         // These are just template examples - actual notifications will be created per user
    //         // This helps document the notification types available
    //     }
    // }
};