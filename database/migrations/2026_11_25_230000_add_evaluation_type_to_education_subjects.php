<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('education_subjects', function (Blueprint $table) {
            $table->string('evaluation_type', 3)->default('kcj')->after('discipline');
        });
    }

    public function down(): void
    {
        Schema::table('education_subjects', function (Blueprint $table) {
            $table->dropColumn('evaluation_type');
        });
    }
};
