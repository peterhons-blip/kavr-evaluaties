<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        // School toevoegen of bestaande school bijwerken
        $schoolId = DB::table('schools')->updateOrInsert(
            ['code' => 'KAVR'],
            [
                'name' => 'Kunstacademie Voeren-Riemst',
                'slug' => 'kunstacademie-voeren-riemst',
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        // School ophalen
        $school = DB::table('schools')
            ->where('code', 'KAVR')
            ->first();

        // Huisstijl en instellingen toevoegen
        DB::table('school_settings')->updateOrInsert(
            ['school_id' => $school->id],
            [
                'slogan' => 'Ruimte voor talent.',
                'subtitle' => 'Beeld. Muziek. Theater.',
                'login_description' =>
                    'Eén plek voor alle evaluaties, leerdoelen en leerlingengroei.',
                'primary_color' => '#142b45',
                'secondary_color' => '#59aaa5',
                'accent_color' => '#e5a65b',
                'language' => 'nl',
                'timezone' => 'Europe/Brussels',
                'updated_at' => now(),
            ]
        );
    }
}
