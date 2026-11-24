<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->restrictOnDelete();
            // DKO3-stamnummers zijn tekst: behoud voorloopnullen (bv. 002012331).
            $table->string('dko3_stamnummer', 32);
            $table->string('first_name', 150);
            $table->string('last_name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Dezelfde leerling kan in meerdere academies voorkomen.
            $table->unique(['school_id', 'dko3_stamnummer'], 'students_school_stamnummer_unique');
            $table->index(['school_id', 'last_name', 'first_name'], 'students_school_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
