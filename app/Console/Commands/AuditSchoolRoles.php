<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Support\SchoolRoleCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditSchoolRoles extends Command
{
    protected $signature = 'artelynx:roles-audit {--repair-defaults : Voeg ontbrekende standaardrollen toe zonder bestaande rollen te overschrijven}';
    protected $description = 'Controleer schoolgebonden rollen en rapporteren van ongeldige koppelingen.';

    public function handle(): int
    {
        if ($this->option('repair-defaults')) {
            foreach (School::cursor() as $school) {
                SchoolRoleCatalog::ensureFor($school);
            }
            $this->info('Ontbrekende standaardrollen toegevoegd.');
        }

        $unknown = DB::table('school_user as su')
            ->leftJoin('school_roles as r', function ($join) {
                $join->on('r.school_id', '=', 'su.school_id')
                    ->on('r.slug', '=', 'su.role');
            })
            ->whereNull('r.id')
            ->select('su.user_id', 'su.school_id', 'su.role')
            ->orderBy('su.school_id')
            ->get();

        if ($unknown->isNotEmpty()) {
            $this->error($unknown->count().' schoolkoppelingen hebben een onbekende rol.');
            $this->table(['Gebruiker ID','School ID','Rol'], $unknown->map(
                fn ($row) => [$row->user_id, $row->school_id, $row->role]
            )->all());

            return self::FAILURE;
        }

        $this->info('OK: alle schoolkoppelingen verwijzen naar bestaande schoolrollen.');
        return self::SUCCESS;
    }
}
