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
        Schema::create('gallery_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('gallery')->onDelete('cascade');
            $table->text('media_url'); // URL del archivo (imagen o video)
            $table->enum('media_type', ['image', 'video'])->default('image');
            $table->integer('order_index')->default(0); // Para ordenar las imágenes/videos
            $table->boolean('is_thumbnail')->default(false); // Imagen principal/thumbnail
            $table->timestamps();
            
            $table->index(['gallery_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_media');
    }
};