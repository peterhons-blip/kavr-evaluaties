<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 60)->nullable();
        });

        // Het DKO3-profiel is schoolgebonden: één personeelslid kan
        // in verschillende academies andere vakken en vestigingen hebben.
        Schema::table('school_user', function (Blueprint $table) {
            $table->text('dko3_roles')->nullable();
            $table->text('dko3_active_assignments')->nullable();
            $table->text('dko3_subject_groups')->nullable();
            $table->text('dko3_locations')->nullable();
            $table->text('dko3_subjects')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('school_user', function (Blueprint $table) {
            $table->dropColumn([
                'dko3_roles', 'dko3_active_assignments',
                'dko3_subject_groups', 'dko3_locations', 'dko3_subjects',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
