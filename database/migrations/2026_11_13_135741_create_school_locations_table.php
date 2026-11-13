<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_locations', function (Blueprint $table) {
            $table->id();

            // Bij welke school hoort de vestiging?
            $table->foreignId('school_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('name');
            $table->string('location_number', 30)->nullable();

            // Adres
            $table->string('street')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city')->nullable();

            // Contact
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();

            // Instellingen
            $table->boolean('is_main_location')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['school_id', 'location_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_locations');
    }
};
