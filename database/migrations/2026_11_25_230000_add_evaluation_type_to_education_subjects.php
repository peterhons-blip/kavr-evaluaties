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

        // Ook reeds aangemaakte Instrument-vakken correct classificeren.
        // Geen persoonsgegevens, evaluaties of gekoppelde inschrijvingen wijzigen.
        \Illuminate\Support\Facades\DB::table('education_subjects')
            ->select('id', 'name')->orderBy('id')
            ->chunk(200, function ($subjects) {
                foreach ($subjects as $subject) {
                    if (\App\Support\DkoEvaluationType::forSubjectName($subject->name) === 'icj') {
                        \Illuminate\Support\Facades\DB::table('education_subjects')
                            ->where('id', $subject->id)->update(['evaluation_type' => 'icj']);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('education_subjects', function (Blueprint $table) {
            $table->dropColumn('evaluation_type');
        });
    }
};
