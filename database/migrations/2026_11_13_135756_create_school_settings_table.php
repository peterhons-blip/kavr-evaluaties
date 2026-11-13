<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            // Loginpagina
            $table->string('slogan')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('login_description')->nullable();

            // Huisstijl
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)
                ->default('#142b45');
            $table->string('secondary_color', 7)
                ->default('#59aaa5');
            $table->string('accent_color', 7)
                ->default('#e5a65b');

            // Algemeen
            $table->string('language', 5)->default('nl');
            $table->string('timezone', 50)
                ->default('Europe/Brussels');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
