<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminSetting;
use App\Models\ServiceSetting;

class FixProductionMigration extends Command
{
    protected $signature = 'production:fix-migration {--force : Force execution even if data exists}';
    protected $description = 'Fix the production migration that failed';

    public function handle()
    {
        $force = $this->option('force');
        
        $this->info('🔧 Ejecutando arreglo de migración de producción...');
        $this->line('');
        
        try {
            // 1. Seed admin settings
            $this->info('1. Creando configuraciones de administrador...');
            $this->seedAdminSettings($force);
            
            // 2. Seed service settings  
            $this->info('2. Creando tipos de servicios...');
            $this->seedServiceSettings($force);
            
            // 3. Create admin users
            $this->info('3. Creando usuarios administradores...');
            $this->seedAdminUsers($force);
            
            // 4. Create gallery items (with correct structure)
            $this->info('4. Creando items de galería...');
            $this->seedGallery($force);
            
            $this->info('');
            $this->info('✅ Migración de producción completada exitosamente!');
            
            // Verify everything
            $this->info('');
            $this->info('🔍 Verificando datos creados...');
            $this->call('production:verify');
            
        } catch (\Exception $e) {
            $this->error('❌ Error durante la migración: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    private function seedAdminSettings($force)
    {
        $settings = [
            'business_hours' => [
                'monday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                'tuesday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                'wednesday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                'thursday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                'friday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                'saturday' => ['open' => '08:00', 'close' => '14:00', 'isOpen' => true],
                'sunday' => ['open' => '08:00', 'close' => '12:00', 'isOpen' => false]
            ],
            'service_settings' => [
                'defaultServiceDuration' => 120,
                'bufferTimeBetweenServices' => 30,
                'minimumAdvanceDays' => 1,
                'enableAdvanceBooking' => true,
                'allowWeekendBooking' => true
            ],
            'pricing_settings' => [
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
            ],
            'notification_settings' => [
                'emailNotifications' => true,
                'clientReminders' => true,
                'adminAlerts' => true,
                'reminderHours' => 24,
                'followUpDays' => 7
            ]
        ];
        
        foreach ($settings as $key => $value) {
            if ($force || !AdminSetting::where('key', $key)->exists()) {
                AdminSetting::set($key, $value);
                $this->line("   ✅ {$key}");
            } else {
                $this->line("   ⏭️ {$key} (ya existe)");
            }
        }
    }
    
    private function seedServiceSettings($force)
    {
        $services = [
            [
                'service_type' => 'residential',
                'duration_minutes' => 180,
                'min_price' => 60.00,
                'max_price' => 150.00,
                'description' => 'Control de Plagas Residencial',
                'is_active' => true
            ],
            [
                'service_type' => 'commercial',
                'duration_minutes' => 180,
                'min_price' => 100.00,
                'max_price' => 500.00,
                'description' => 'Control de Plagas Comercial',
                'is_active' => true
            ],
            [
                'service_type' => 'industrial',
                'duration_minutes' => 240,
                'min_price' => 200.00,
                'max_price' => 1000.00,
                'description' => 'Control de Plagas Industrial',
                'is_active' => true
            ],
            [
                'service_type' => 'emergency',
                'duration_minutes' => 60,
                'min_price' => 80.00,
                'max_price' => 300.00,
                'description' => 'Servicio de Emergencia',
                'is_active' => true
            ]
        ];
        
        foreach ($services as $serviceData) {
            if ($force || !ServiceSetting::where('service_type', $serviceData['service_type'])->exists()) {
                ServiceSetting::create($serviceData);
                $this->line("   ✅ {$serviceData['service_type']}");
            } else {
                $this->line("   ⏭️ {$serviceData['service_type']} (ya existe)");
            }
        }
    }
    
    private function seedAdminUsers($force)
    {
        $users = [
            [
                'name' => 'Kevin Villacreses',
                'email' => 'kevinvillajim@hotmail.com',
                'password' => 'Olvidon2@',
                'phone' => '593963368896'
            ],
            [
                'name' => 'Efraín Villacreses',
                'email' => 'efravillacrses@gmail.com',
                'password' => 'Olvidon1@',
                'phone' => '593995031066'
            ]
        ];
        
        foreach ($users as $userData) {
            $exists = DB::table('users')->where('email', $userData['email'])->exists();
            
            if ($force || !$exists) {
                DB::table('users')->updateOrInsert(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => Hash::make($userData['password']),
                        'role' => 'admin',
                        'phone' => $userData['phone'],
                        'city' => 'Quito',
                        'email_verified_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                $this->line("   ✅ {$userData['name']} ({$userData['email']})");
            } else {
                $this->line("   ⏭️ {$userData['name']} (ya existe)");
            }
        }
    }
    
    private function seedGallery($force)
    {
        // Check if gallery table exists and has the required columns
        if (!\Schema::hasTable('gallery')) {
            $this->comment("   ⏭️ Tabla gallery no existe, saltando...");
            return;
        }
        
        $galleryItems = [
            [
                'title' => 'Control de Plagas Residencial',
                'description' => 'Tratamiento profesional en hogares',
                'image_url' => '/storage/gallery/residential-default.jpg',
                'video_url' => null,
                'media_type' => 'image',
                'category' => 'fumigacion_residencial',
                'is_active' => 1,
                'featured' => 1
            ],
            [
                'title' => 'Control de Plagas Comercial',
                'description' => 'Soluciones para empresas y negocios',
                'image_url' => '/storage/gallery/commercial-default.jpg',
                'video_url' => null,
                'media_type' => 'image',
                'category' => 'fumigacion_comercial',
                'is_active' => 1,
                'featured' => 1
            ],
            [
                'title' => 'Control de Plagas Industrial',
                'description' => 'Tratamientos especializados industriales',
                'image_url' => '/storage/gallery/industrial-default.jpg',
                'video_url' => null,
                'media_type' => 'image',
                'category' => 'fumigacion_industrial',
                'is_active' => 1,
                'featured' => 1
            ],
            [
                'title' => 'Servicio de Emergencia',
                'description' => 'Atención inmediata para infestaciones severas',
                'image_url' => '/storage/gallery/emergency-default.jpg',
                'video_url' => null,
                'media_type' => 'image',
                'category' => 'emergencia',
                'is_active' => 1,
                'featured' => 1
            ]
        ];
        
        foreach ($galleryItems as $item) {
            $exists = DB::table('gallery')->where('title', $item['title'])->exists();
            
            if ($force || !$exists) {
                DB::table('gallery')->updateOrInsert(
                    ['title' => $item['title']],
                    array_merge($item, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ])
                );
                $this->line("   ✅ {$item['title']}");
            } else {
                $this->line("   ⏭️ {$item['title']} (ya existe)");
            }
        }
    }
}