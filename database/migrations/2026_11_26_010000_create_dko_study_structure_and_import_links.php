<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('education_domain_id')->constrained('education_domains')->restrictOnDelete();
            $table->string('name', 160);
            $table->string('kind', 40)->default('regular'); // regular, short, specialization
            $table->string('audience', 40)->nullable(); // youth, adult, both
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'education_domain_id', 'name', 'kind'], 'program_name_school_unique');
        });
        Schema::create('education_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('education_program_id')->constrained('education_programs')->cascadeOnDelete();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['education_program_id','name']);
        });
        Schema::create('education_grade_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('education_program_id')->constrained('education_programs')->cascadeOnDelete();
            $table->foreignId('education_option_id')->nullable()->constrained('education_options')->nullOnDelete();
            $table->string('code', 20); // 2.1, 3.5, C.1, S.1
            $table->string('grade', 20)->nullable();
            $table->unsignedTinyInteger('study_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['education_program_id', 'education_option_id', 'code'], 'grade_year_option_code_unique');
        });
        Schema::create('student_domain_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_school_year_id')->constrained('student_school_years')->cascadeOnDelete();
            $table->foreignId('education_domain_id')->constrained('education_domains')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['student_school_year_id', 'education_domain_id'], 'student_year_domain_unique');
            $table->index(['school_id', 'is_active']);
        });
        // Meerdere docenten/vestigingen per vakinschrijving blijven afzonderlijk.
        Schema::create('student_subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_subject_enrollment_id')->constrained('student_subject_enrollments')->cascadeOnDelete();
            $table->foreignId('teacher_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('school_location_id')->constrained('school_locations')->restrictOnDelete();
            $table->string('grade_code', 20);
            $table->timestamps();
            $table->unique(['student_subject_enrollment_id','teacher_user_id','school_location_id','grade_code'], 'subject_teacher_location_grade_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject_assignments');
        Schema::dropIfExists('student_domain_enrollments');
        Schema::dropIfExists('education_grade_years');
        Schema::dropIfExists('education_options');
        Schema::dropIfExists('education_programs');
    }
};
