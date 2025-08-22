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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('certificate_number')->unique();
            $table->string('file_name');
            $table->string('file_path');
            $table->date('issue_date');
            $table->date('valid_until');
            $table->string('type');
            $table->enum('status', ['pending', 'valid', 'expired', 'revoked'])->default('pending');
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('certificate_number');
            $table->index('valid_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};