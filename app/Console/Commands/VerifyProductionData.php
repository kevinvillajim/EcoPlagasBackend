<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminSetting;
use App\Models\ServiceSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VerifyProductionData extends Command
{
    protected $signature = 'production:verify {--fix : Fix missing data}';
    protected $description = 'Verify and optionally fix essential production data';

    public function handle()
    {
        $fix = $this->option('fix');
        $issues = 0;
        
        $this->info('🔍 Verificando datos esenciales para producción...');
        $this->line('');
        
        // 1. Check Admin Settings
        $this->info('1. Verificando configuraciones de administrador...');
        $requiredSettings = ['business_hours', 'service_settings', 'pricing_settings', 'notification_settings'];
        
        foreach ($requiredSettings as $setting) {
            $exists = AdminSetting::where('key', $setting)->exists();
            if ($exists) {
                $this->line("   ✅ {$setting}");
            } else {
                $this->error("   ❌ {$setting} - FALTANTE");
                $issues++;
                
                if ($fix) {
                    $this->fixAdminSetting($setting);
                }
            }
        }
        
        // 2. Check Service Settings
        $this->info('');
        $this->info('2. Verificando tipos de servicios...');
        $requiredServices = ['residential', 'commercial', 'industrial', 'emergency'];
        
        foreach ($requiredServices as $serviceType) {
            $exists = ServiceSetting::where('service_type', $serviceType)->where('is_active', true)->exists();
            if ($exists) {
                $this->line("   ✅ {$serviceType}");
            } else {
                $this->error("   ❌ {$serviceType} - FALTANTE O INACTIVO");
                $issues++;
                
                if ($fix) {
                    $this->fixServiceSetting($serviceType);
                }
            }
        }
        
        // 3. Check Admin User
        $this->info('');
        $this->info('3. Verificando usuario administrador...');
        $adminCount = User::where('role', 'admin')->count();
        
        if ($adminCount > 0) {
            $this->line("   ✅ {$adminCount} administrador(es) activo(s)");
        } else {
            $this->error("   ❌ No hay administradores activos");
            $issues++;
            
            if ($fix) {
                $this->createDefaultAdmin();
            }
        }
        
        // 4. Check Database Tables
        $this->info('');
        $this->info('4. Verificando tablas esenciales...');
        $requiredTables = [
            'users', 'services', 'admin_settings', 'service_settings', 
            'reviews', 'gallery', 'notifications', 'certificates'
        ];
        
        foreach ($requiredTables as $table) {
            if (\Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->line("   ✅ {$table} ({$count} registros)");
            } else {
                $this->error("   ❌ Tabla {$table} no existe");
                $issues++;
            }
        }
        
        // 5. Check critical configurations
        $this->info('');
        $this->info('5. Verificando configuraciones críticas...');
        
        // Check if service settings are properly formatted
        $adminSettings = AdminSetting::getAllSettings();
        
        if (isset($adminSettings['serviceSettings']) && is_array($adminSettings['serviceSettings'])) {
            $this->line("   ✅ Configuraciones de servicio válidas");
        } else {
            $this->error("   ❌ Configuraciones de servicio inválidas");
            $issues++;
            
            if ($fix) {
                $this->info("   🔧 Ejecutando comando de reparación...");
                $this->call('admin:fix-settings');
            }
        }
        
        // Summary
        $this->info('');
        $this->line('=' . str_repeat('=', 50));
        
        if ($issues === 0) {
            $this->info('🎉 ¡Todos los datos esenciales están presentes y válidos!');
            $this->line('✅ La plataforma está lista para producción.');
        } else {
            if ($fix) {
                $this->info("🔧 Se encontraron y corrigieron {$issues} problema(s).");
                $this->line('🔄 Ejecuta el comando nuevamente para verificar las correcciones.');
            } else {
                $this->error("⚠️ Se encontraron {$issues} problema(s).");
                $this->line('💡 Ejecuta con --fix para intentar corregirlos automáticamente:');
                $this->comment('   php artisan production:verify --fix');
            }
        }
        
        // Production checklist
        if ($issues === 0) {
            $this->info('');
            $this->info('📋 Lista de verificación para producción:');
            $this->line('   ✅ Datos esenciales verificados');
            $this->line('   🔧 Pasos adicionales recomendados:');
            $this->line('     • Verificar configuración de correo electrónico');
            $this->line('     • Configurar certificados SSL');
            $this->line('     • Configurar copias de seguridad automáticas');
            $this->line('     • Verificar configuración de CORS para el dominio de producción');
            $this->line('     • Cambiar contraseñas por defecto');
        }
        
        return $issues === 0 ? 0 : 1;
    }
    
    private function fixAdminSetting($settingKey)
    {
        $this->info("   🔧 Creando configuración {$settingKey}...");
        
        $defaults = [
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
        
        if (isset($defaults[$settingKey])) {
            AdminSetting::set($settingKey, $defaults[$settingKey]);
            $this->line("   ✅ Configuración {$settingKey} creada");
        }
    }
    
    private function fixServiceSetting($serviceType)
    {
        $this->info("   🔧 Creando servicio {$serviceType}...");
        
        $services = [
            'residential' => [
                'duration_minutes' => 180,
                'min_price' => 60.00,
                'max_price' => 150.00,
                'description' => 'Control de Plagas Residencial',
                'is_active' => true
            ],
            'commercial' => [
                'duration_minutes' => 180,
                'min_price' => 100.00,
                'max_price' => 500.00,
                'description' => 'Control de Plagas Comercial',
                'is_active' => true
            ],
            'industrial' => [
                'duration_minutes' => 240,
                'min_price' => 200.00,
                'max_price' => 1000.00,
                'description' => 'Control de Plagas Industrial',
                'is_active' => true
            ],
            'emergency' => [
                'duration_minutes' => 60,
                'min_price' => 80.00,
                'max_price' => 300.00,
                'description' => 'Servicio de Emergencia',
                'is_active' => true
            ]
        ];
        
        if (isset($services[$serviceType])) {
            ServiceSetting::updateOrCreate(
                ['service_type' => $serviceType],
                $services[$serviceType]
            );
            $this->line("   ✅ Servicio {$serviceType} creado/actualizado");
        }
    }
    
    private function createDefaultAdmin()
    {
        $this->info("   🔧 Creando usuario administrador por defecto...");
        
        User::create([
            'name' => 'Administrador EcoPlagas',
            'email' => 'admin@ecoplagasecuador.com',
            'password' => Hash::make('EcoPlagas2025!'),
            'role' => 'admin',
            'phone' => '+593999000000',
            'city' => 'Quito',
            'email_verified_at' => now()
        ]);
        
        $this->line("   ✅ Usuario administrador creado");
        $this->comment("   📧 Email: admin@ecoplagasecuador.com");
        $this->comment("   🔒 Password: EcoPlagas2025!");
        $this->error("   ⚠️  IMPORTANTE: Cambia la contraseña después del primer login");
    }
}