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
        Schema::table('users', function (Blueprint $table) {
            $table->string('document_type')->nullable()->after('address');
            $table->string('document_number')->nullable()->after('document_type');
            $table->date('birth_date')->nullable()->after('document_number');
            $table->json('preferences')->nullable()->after('birth_date');
            $table->json('security_settings')->nullable()->after('preferences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'document_type',
                'document_number', 
                'birth_date',
                'preferences',
                'security_settings'
            ]);
        });
    }
};
