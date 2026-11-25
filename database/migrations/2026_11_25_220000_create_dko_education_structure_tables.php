<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'name']);
            $table->index(['school_id', 'is_active']);
        });

        Schema::create('education_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('education_domain_id')->constrained('education_domains')->restrictOnDelete();
            $table->string('name', 200);
            $table->string('code', 60)->nullable();
            $table->string('discipline', 120)->nullable(); // instrument / specialisatie indien van toepassing
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'education_domain_id', 'name', 'discipline'], 'subject_school_domain_name_unique');
            $table->index(['school_id', 'is_active']);
        });

        // Leerlingen blijven bestaan, ook wanneer ze niet meer ingeschreven zijn.
        // Een apart schooljaarrecord bewaart de historiek zonder inlogaccounts.
        Schema::create('student_school_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('school_year', 9); // 2026-2027
            $table->string('status', 20)->default('enrolled'); // enrolled, ended
            $table->timestamps();
            $table->unique(['student_id', 'school_year']);
            $table->index(['school_id', 'school_year', 'status']);
        });

        Schema::create('student_subject_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_school_year_id')->constrained('student_school_years')->cascadeOnDelete();
            $table->foreignId('education_subject_id')->constrained('education_subjects')->restrictOnDelete();
            $table->string('grade', 80)->nullable();
            $table->string('study_year', 40)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['student_school_year_id', 'education_subject_id'], 'student_year_subject_unique');
            $table->index(['school_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject_enrollments');
        Schema::dropIfExists('student_school_years');
        Schema::dropIfExists('education_subjects');
        Schema::dropIfExists('education_domains');
    }
};
