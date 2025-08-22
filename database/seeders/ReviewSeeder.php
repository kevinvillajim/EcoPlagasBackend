<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Service;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user for moderation
        $admin = User::where('role', 'admin')->first();
        
        // Create some sample services first if they don't exist
        $this->createSampleServices();
        
        // Get some users and services for the reviews
        $users = User::where('role', 'client')->take(10)->get();
        $services = Service::where('status', 'completed')->take(20)->get();

        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios client disponibles para crear reseñas.');
            return;
        }
        
        if ($services->isEmpty()) {
            $this->command->warn('No hay servicios completados disponibles para crear reseñas.');
            return;
        }

        $reviewsData = [
            [
                'rating' => 5,
                'comment' => 'Excelente servicio, muy profesionales y puntuales. El problema de cucarachas se solucionó completamente. El técnico fue muy educado y explicó todo el proceso detalladamente.',
                'location' => 'Guayaquil - Norte',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => true,
                'helpful_count' => 42,
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'rating' => 5,
                'comment' => 'Contratamos sus servicios para nuestro restaurante y quedamos muy satisfechos. Trabajo discreto, efectivo y con todas las certificaciones requeridas. Personal muy profesional.',
                'location' => 'Samborondón',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => true,
                'helpful_count' => 38,
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'rating' => 5,
                'comment' => 'Increíble resultado! Tenía hormigas por toda la casa y después del tratamiento no he vuelto a ver ni una. Productos seguros para mis hijos y mascotas. Recomendado 100%.',
                'location' => 'Vía a la Costa',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => true,
                'helpful_count' => 35,
                'created_at' => Carbon::now()->subDays(10),
            ],
            [
                'rating' => 5,
                'comment' => 'Servicio de desinfección completo para mi oficina. Personal capacitado, productos certificados y resultados garantizados. Totalmente recomendados.',
                'location' => 'Centro de Guayaquil',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => true,
                'helpful_count' => 29,
                'created_at' => Carbon::now()->subDays(12),
            ],
            [
                'rating' => 5,
                'comment' => 'Tuve un problema serio con ratones en mi bodega. EcoPlagas resolvió el problema rápidamente y me dieron consejos para prevenir futuras infestaciones.',
                'location' => 'Duran',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 31,
                'created_at' => Carbon::now()->subDays(15),
            ],
            [
                'rating' => 5,
                'comment' => 'Servicio excelente. Llegaron puntuales, trabajaron de manera muy limpia y ordenada. Los productos no tienen olores fuertes y son seguros.',
                'location' => 'Los Ceibos',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 27,
                'created_at' => Carbon::now()->subDays(18),
            ],
            [
                'rating' => 4,
                'comment' => 'Buen servicio en general. El problema se resolvió aunque tardaron un poco más de lo esperado. El personal fue amable y profesional.',
                'location' => 'Kennedy Norte',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 18,
                'created_at' => Carbon::now()->subDays(20),
            ],
            [
                'rating' => 5,
                'comment' => 'Contraté el servicio para mi consultorio médico. Cumplieron con todas las normas de bioseguridad y me entregaron los certificados correspondientes.',
                'location' => 'Urdesa',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 33,
                'created_at' => Carbon::now()->subDays(25),
            ],
            [
                'rating' => 5,
                'comment' => 'Las hormigas habían invadido mi jardín y cocina. Después del tratamiento, desaparecieron completamente. El técnico me explicó el proceso y fue muy amable.',
                'location' => 'Alborada',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 25,
                'created_at' => Carbon::now()->subDays(30),
            ],
            [
                'rating' => 5,
                'comment' => 'Excelente servicio para mi hotel. Trabajo discreto, sin interrumpir las operaciones. Los huéspedes no se dieron cuenta del tratamiento.',
                'location' => 'Malecón 2000',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 41,
                'created_at' => Carbon::now()->subDays(35),
            ],
            [
                'rating' => 4,
                'comment' => 'Buen servicio, resolvieron el problema de ratones en mi casa. El precio es justo y el personal muy educado. Solo me gustaría que dieran más seguimiento.',
                'location' => 'Mapasingue',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 16,
                'created_at' => Carbon::now()->subDays(40),
            ],
            [
                'rating' => 5,
                'comment' => 'Servicio impecable para mi clínica dental. Cumplieron con todos los protocolos de bioseguridad. Personal muy capacitado y productos de alta calidad.',
                'location' => 'Ceibos Norte',
                'status' => 'auto_approved',
                'is_auto_approved' => true,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 30,
                'created_at' => Carbon::now()->subDays(45),
            ],
            [
                'rating' => 3,
                'comment' => 'El servicio fue bueno pero tardaron más de lo esperado en completar el trabajo. El resultado final fue satisfactorio.',
                'location' => 'Bastión Popular',
                'status' => 'pending',
                'is_auto_approved' => false,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 8,
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'rating' => 2,
                'comment' => 'No estoy satisfecha con el resultado. El problema persistió después del servicio y tuve que llamar de nuevo.',
                'location' => 'Monte Sinaí',
                'status' => 'pending',
                'is_auto_approved' => false,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 3,
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'rating' => 1,
                'comment' => 'Muy mal servicio, no solucionaron el problema y fueron impuntuales. No recomiendo para nada.',
                'location' => 'Guasmo',
                'status' => 'rejected',
                'is_auto_approved' => false,
                'verified' => true,
                'is_featured' => false,
                'helpful_count' => 0,
                'moderated_by' => $admin->id ?? null,
                'moderated_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(4),
            ],
        ];

        foreach ($reviewsData as $index => $reviewData) {
            $user = $users->get($index % $users->count());
            $service = $services->get($index % $services->count());

            $reviewData['user_id'] = $user->id;
            $reviewData['service_id'] = $service->id;
            $reviewData['is_public'] = true;
            
            // Set moderated_at for auto-approved reviews
            if ($reviewData['is_auto_approved']) {
                $reviewData['moderated_at'] = $reviewData['created_at'];
            }

            Review::create($reviewData);
        }

        $this->command->info('ReviewSeeder: Se crearon ' . count($reviewsData) . ' reseñas de prueba.');
    }

    private function createSampleServices()
    {
        // Only create services if none exist
        if (Service::count() > 0) {
            return;
        }

        // Get users for services
        $clients = User::where('role', 'client')->get();
        $admin = User::where('role', 'admin')->first();

        if ($clients->isEmpty() || !$admin) {
            return;
        }

        $serviceTypes = [
            'fumigacion_residencial',
            'control_roedores', 
            'control_hormigas',
            'desinfeccion',
            'control_voladores',
            'fumigacion_comercial'
        ];

        $addresses = [
            'Guayaquil - Norte, Av. Francisco de Orellana',
            'Samborondón, Vía Samborondón',
            'Vía a la Costa, Km 15',
            'Centro de Guayaquil, Av. 9 de Octubre',
            'Duran, Centro Comercial',
            'Los Ceibos, Urbanización privada',
            'Kennedy Norte, Conjunto residencial',
            'Urdesa, Casa familiar',
            'Alborada, Edificio comercial',
            'Malecón 2000, Hotel boutique'
        ];

        for ($i = 0; $i < 25; $i++) {
            $client = $clients->random();
            $serviceType = $serviceTypes[array_rand($serviceTypes)];
            $address = $addresses[array_rand($addresses)];
            
            Service::create([
                'user_id' => $client->id,
                'technician_id' => $admin->id, // Using admin as technician for now
                'type' => $serviceType,
                'description' => 'Servicio de ' . str_replace('_', ' ', $serviceType) . ' realizado exitosamente.',
                'address' => $address,
                'scheduled_date' => Carbon::now()->subDays(rand(1, 60)),
                'completed_date' => Carbon::now()->subDays(rand(1, 50)),
                'status' => 'completed',
                'cost' => rand(50, 300),
                'notes' => 'Servicio completado satisfactoriamente. Cliente satisfecho con los resultados.',
            ]);
        }

        $this->command->info('Se crearon 25 servicios de muestra.');
    }
}
