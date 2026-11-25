<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Schoolspecifieke catalogus uit KA-VR DKO3 werklijst (2026-2027).
        // Idempotent; bestaande instellingen en vakken worden nooit overschreven.
        $schoolIds = DB::table('schools')
            ->where(fn ($q) => $q->where('name', 'like', '%Voeren%Riemst%')
                ->orWhereIn('code', ['KA-VR','KAVR']))->pluck('id');
        $catalog = [
            'Be' => [
                'Beeldatelier'
            ],
            'Wo' => [
                'Dramastudio',
                'Kunst- en cultuuratelier',
                'Schrijven',
                'Speltheater',
                'Story-telling',
                'Woordatelier'
            ],
            'Mu' => [
                'Accordeon (klassiek)',
                'Altsaxofoon (klassiek)',
                'BP orgel (klassiek)',
                'BP piano (klassiek)',
                'Barokhobo (oude)',
                'Basgitaar (jpr)',
                'Basklarinet (klassiek)',
                'Bastuba (klassiek)',
                'Bugel (klassiek)',
                'Cello (klassiek)',
                'Compositie (klassiek)',
                'Dwarsfluit (klassiek)',
                'Elektrische en/of akoestische gitaar (jpr)',
                'Engelse hoorn (klassiek)',
                'Eufonium (klassiek)',
                'Fagot (klassiek)',
                'Gitaar (klassiek)',
                'Groepsmusiceren (alc)',
                'Groepsmusiceren instrumentaal (jpr)',
                'Groepsmusiceren instrumentaal (klassiek)',
                'Groepsmusiceren vocaal (jpr)',
                'Groepsmusiceren vocaal (klassiek)',
                'Hobo (klassiek)',
                'Hobo d\'amore (oude)',
                'Hoorn (klassiek)',
                'Kids-academie',
                'Klarinet (klassiek)',
                'Klavierklas (klassiek)',
                'MCV (jpr)',
                'MCV (klassiek)',
                'Melodisch slagwerk (klassiek)',
                'Muziekleer',
                'Muziektheorie',
                'Orkestslagwerk (klassiek)',
                'Piano (jpr)',
                'Piano (klassiek)',
                'Piccolo (klassiek)',
                'Ritmisch slagwerk (klassiek)',
                'Singer-songwriter',
                'Sopraansaxofoon (klassiek)',
                'Tenorsaxofoon (klassiek)',
                'Trombone (klassiek)',
                'Trompet (klassiek)',
                'Viool (klassiek)',
                'Zang (jpr)',
                'Zang (klassiek)',
                'Zang (oude)'
            ],
        ];
        $instrumentPrefixes = [
                'Accordeon',
                'Altsaxofoon',
                'Barokhobo',
                'Basgitaar',
                'Basklarinet',
                'Bastuba',
                'Bugel',
                'Cello',
                'Dwarsfluit',
                'Elektrische en/of akoestische gitaar',
                'Engelse hoorn',
                'Eufonium',
                'Fagot',
                'Gitaar',
                'Hobo',
                'Hobo d\'amore',
                'Hoorn',
                'Klarinet',
                'Melodisch slagwerk',
                'Orkestslagwerk',
                'Piano',
                'Piccolo',
                'Ritmisch slagwerk',
                'Sopraansaxofoon',
                'Tenorsaxofoon',
                'Trombone',
                'Trompet',
                'Viool'
        ];
        foreach ($schoolIds as $schoolId) {
            foreach ($catalog as $domainCode => $names) {
                $domain = DB::table('education_domains')->where('school_id',$schoolId)
                    ->where('code',$domainCode)->first();
                if (! $domain) continue;
                foreach ($names as $name) {
                    $exists = DB::table('education_subjects')
                        ->where('school_id',$schoolId)
                        ->where('education_domain_id',$domain->id)
                        ->where('name',$name)->exists();
                    if ($exists) continue;
                    $isInstrument = false;
                    foreach ($instrumentPrefixes as $prefix) {
                        if ($name === $prefix || str_starts_with($name,$prefix.' (')) {
                            $isInstrument = true;
                            break;
                        }
                    }
                    DB::table('education_subjects')->insert([
                        'school_id'=>$schoolId, 'education_domain_id'=>$domain->id,
                        'name'=>$name, 'code'=>null, 'discipline'=>null,
                        'evaluation_type'=>$isInstrument ? 'icj' : 'kcj',
                        'is_active'=>true, 'created_at'=>now(), 'updated_at'=>now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Historische catalogusrecords nooit via migratierollback verwijderen.
    }
};
