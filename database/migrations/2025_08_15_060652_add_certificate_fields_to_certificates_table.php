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
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('client_name')->nullable();
            $table->string('client_ruc')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('treated_area')->nullable();
            
            // Servicios realizados
            $table->boolean('desinsectacion')->default(false);
            $table->boolean('desinfeccion')->default(false);
            $table->boolean('desratizacion')->default(false);
            $table->boolean('otro_servicio')->default(false);
            
            // Productos y categorías por servicio
            $table->string('producto_desinsectacion')->nullable();
            $table->string('categoria_desinsectacion')->nullable();
            $table->string('registro_desinsectacion')->nullable();
            
            $table->string('producto_desinfeccion')->nullable();
            $table->string('categoria_desinfeccion')->nullable();
            $table->string('registro_desinfeccion')->nullable();
            
            $table->string('producto_desratizacion')->nullable();
            $table->string('categoria_desratizacion')->nullable();
            $table->string('registro_desratizacion')->nullable();
            
            $table->string('producto_otro')->nullable();
            $table->string('categoria_otro')->nullable();
            $table->string('registro_otro')->nullable();
            
            // Información adicional
            $table->text('service_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Solo eliminar las columnas que realmente se agregaron en el método up()
            $columns = [
                'client_name', 'client_ruc', 'address', 'city', 'phone', 'treated_area',
                'desinsectacion', 'desinfeccion', 'desratizacion', 'otro_servicio',
                'producto_desinsectacion', 'categoria_desinsectacion', 'registro_desinsectacion',
                'producto_desinfeccion', 'categoria_desinfeccion', 'registro_desinfeccion',
                'producto_desratizacion', 'categoria_desratizacion', 'registro_desratizacion',
                'producto_otro', 'categoria_otro', 'registro_otro',
                'service_description'
            ];
            
            // Verificar y eliminar solo las columnas que existen
            foreach ($columns as $column) {
                if (Schema::hasColumn('certificates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
