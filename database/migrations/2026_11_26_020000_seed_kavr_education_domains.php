<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alleen KA-VR: deze domeinen horen bij hun gecontroleerde DKO3-export.
        // Geen automatische voorbeelddata bij andere tenants.
        $schools = DB::table('schools')
            ->where(function ($query) {
                $query->where('name', 'like', '%Voeren%Riemst%')
                    ->orWhereIn('code', ['KA-VR', 'KAVR']);
            })->get(['id']);
        foreach ($schools as $school) {
            foreach ([
                ['name' => 'Muziek', 'code' => 'Mu'],
                ['name' => 'Beeld', 'code' => 'Be'],
                ['name' => 'Woord', 'code' => 'Wo'],
            ] as $domain) {
                // Zoek eerst op officiële code of naam om bestaande data niet te dupliceren.
                $existing = DB::table('education_domains')
                    ->where('school_id', $school->id)
                    ->where(function ($q) use ($domain) {
                        $q->where('code', $domain['code'])
                          ->orWhere('name', $domain['name']);
                    })->first();
                if (! $existing) {
                    DB::table('education_domains')->insert([
                        'school_id' => $school->id,
                        'name' => $domain['name'],
                        'code' => $domain['code'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Bewust niets verwijderen: domeinen kunnen inmiddels in vakken,
        // inschrijvingen en historische evaluaties gebruikt worden.
    }
};
