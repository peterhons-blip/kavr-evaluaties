<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_subject_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('education_grade_year_id')->constrained('education_grade_years')->cascadeOnDelete();
            $table->foreignId('education_subject_id')->constrained('education_subjects')->restrictOnDelete();
            $table->string('school_year', 9);
            $table->boolean('is_elective')->default(false);
            $table->decimal('hours_per_week', 4, 1)->nullable();
            $table->boolean('allow_alternative_context')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['education_grade_year_id','education_subject_id','school_year'], 'grade_subject_school_year_unique');
            $table->index(['school_id','school_year','is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_subject_offerings');
    }
};
