<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_user', function (Blueprint $table) {
            $table->id();

            // Gebruiker
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // School
            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            // Rol binnen deze school
            $table->string('role', 30)->default('teacher');

            // Lidmaatschap actief?
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Geen dubbele koppelingen
            $table->unique(['user_id', 'school_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_user');
    }
};
