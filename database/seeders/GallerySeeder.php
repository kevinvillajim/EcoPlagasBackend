<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gallery;
use Carbon\Carbon;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data safely
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Gallery::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $galleryItems = [
            [
                'title' => 'Control de Roedores - Restaurante Centro',
                'description' => 'Exitoso tratamiento integral contra roedores en importante restaurante del centro de Guayaquil. Eliminación completa en 48 horas con productos eco-amigables.',
                'image_url' => './servicios1.jpg',
                'category' => 'control_roedores',
                'is_active' => true,
                'featured' => true
            ],
            [
                'title' => 'Fumigación Residencial - Casa Familiar',
                'description' => 'Tratamiento completo de fumigación en vivienda familiar. Control efectivo de cucarachas y hormigas con total seguridad para niños y mascotas.',
                'image_url' => './servicios2.jpg',
                'category' => 'fumigacion_residencial',
                'is_active' => true,
                'featured' => true
            ],
            [
                'title' => 'Control de Hormigas - Jardín Residencial',
                'description' => 'Tratamiento especializado para eliminación de colonias de hormigas en jardín y áreas exteriores. Resultados duraderos sin dañar las plantas.',
                'image_url' => './servicios3.jpg',
                'category' => 'control_hormigas',
                'is_active' => true,
                'featured' => false
            ],
            [
                'title' => 'Desinfección de Oficinas',
                'description' => 'Servicio de desinfección completa para oficinas corporativas. Eliminación de bacterias, virus y control preventivo de plagas.',
                'image_url' => './servicios4.jpg',
                'category' => 'desinfeccion',
                'is_active' => true,
                'featured' => true
            ],
            [
                'title' => 'Control de Voladores - Hotel Boutique',
                'description' => 'Eliminación de insectos voladores en hotel boutique. Tratamiento discreto que no afecta la experiencia de los huéspedes.',
                'image_url' => './servicios5.jpg',
                'category' => 'control_voladores',
                'is_active' => true,
                'featured' => false
            ],
            [
                'title' => 'Fumigación Comercial - Centro Comercial',
                'description' => 'Tratamiento integral en centro comercial de gran tamaño. Control de múltiples tipos de plagas con cronograma adaptado al horario comercial.',
                'image_url' => './clientes1.jpg',
                'category' => 'fumigacion_comercial',
                'is_active' => true,
                'featured' => true
            ],
            [
                'title' => 'Control de Roedores - Almacén Industrial',
                'description' => 'Sistema de control de roedores en almacén de alimentos. Implementación de programa de monitoreo continuo y control sanitario.',
                'image_url' => './clientes2.jpg',
                'category' => 'control_roedores',
                'is_active' => true,
                'featured' => false
            ],
            [
                'title' => 'Desinfección General - Clínica Médica',
                'description' => 'Desinfección especializada en clínica médica. Protocolos hospitalarios con productos certificados para área de salud.',
                'image_url' => './why2.jpg',
                'category' => 'desinfeccion',
                'is_active' => true,
                'featured' => true
            ],
            [
                'title' => 'Control de Hormigas - Complejo Residencial',
                'description' => 'Tratamiento en complejo residencial de 200 unidades. Plan integral con garantía extendida y seguimiento mensual.',
                'image_url' => './why3.jpg',
                'category' => 'control_hormigas',
                'is_active' => true,
                'featured' => false
            ],
            [
                'title' => 'Fumigación Residencial - Casa de Campo',
                'description' => 'Tratamiento especializado en casa de campo con jardines extensos. Control ecológico respetando el ecosistema natural.',
                'image_url' => './why4.jpg',
                'category' => 'fumigacion_residencial',
                'is_active' => true,
                'featured' => false
            ]
        ];

        foreach ($galleryItems as $item) {
            Gallery::create($item);
        }
    }
}