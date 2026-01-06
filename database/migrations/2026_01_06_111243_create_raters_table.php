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
        Schema::create('raters', function (Blueprint $table) {
            $table->id();
            $table->string('nik_hash')->index(); // Hashed NIK for privacy
            $table->string('full_name');
            $table->string('phone_encrypted'); // Encrypted phone
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('relationship_to_patient')->nullable(); // Keluarga, Pasien, dll
            $table->date('visit_date')->nullable();
            $table->string('service_unit')->nullable();
            $table->boolean('consent_given')->default(false);
            $table->timestamps();

            // Composite index for duplicate checking
            $table->index(['nik_hash', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raters');
    }
};
