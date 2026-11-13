<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();

            // Identificatie
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->string('slug', 100)->unique();

            // Officiele gegevens
            $table->string('institution_number', 30)
                ->nullable()
                ->unique();

            // Contactgegevens
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('website')->nullable();

            // Administratieve hoofdzetel
            $table->string('street')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('country_code', 2)->default('BE');

            // Directie (voorlopig tekst)
            $table->string('director_name')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
