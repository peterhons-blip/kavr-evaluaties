<?php

namespace App\Support;

use App\Models\School;
use Illuminate\Support\Facades\DB;

final class SchoolRoleCatalog
{
    /** Permissions are checked on the server, never inferred from menus. */
    public const PERMISSIONS = [
        'users.view' => 'Gebruikers bekijken',
        'users.manage' => 'Gebruikers en schoolkoppelingen beheren',
        'roles.manage' => 'Schoolrollen en rechten beheren',
        'school.settings' => 'Schoolinstellingen beheren',
        'students.view' => 'Leerlingen bekijken',
        'students.manage' => 'Leerlingen beheren',
        'groups.view' => 'Lesgroepen bekijken',
        'groups.manage' => 'Lesgroepen beheren',
        'goals.manage' => 'Leerdoelen beheren',
        'evaluations.view' => 'Evaluaties bekijken',
        'evaluations.write' => 'Evaluaties invoeren',
        'evaluations.publish' => 'Evaluaties definitief maken',
        'reports.view' => 'Rapporten bekijken',
        'reports.export' => 'Rapporten exporteren',
    ];

    /**
     * Overzicht voor de matrix: categorie -> onderdeel -> functie.
     * Enkel de rechten in PERMISSIONS geven echt toegangscontrole.
     */
    public const CATEGORIES = [
        'Organisatie' => [
            'Personeelsleden' => ['users.view', 'users.manage'],
            'Rollen en rechten' => ['roles.manage'],
            'Schoolinstellingen' => ['school.settings'],
        ],
        'Onderwijs' => [
            'Leerlingen' => ['students.view', 'students.manage'],
            'Lesgroepen' => ['groups.view', 'groups.manage'],
            'Leerdoelen' => ['goals.manage'],
            'Evaluaties' => ['evaluations.view', 'evaluations.write', 'evaluations.publish'],
        ],
        'Rapportering' => [
            'Rapporten en DKO3-export' => ['reports.view', 'reports.export'],
        ],
    ];

    public static function defaults(): array
    {
        $all = array_keys(self::PERMISSIONS);

        return [
            'school_admin' => ['name' => 'Schoolbeheerder', 'description' => 'Beheer van de eigen school', 'permissions' => $all],
            'director' => ['name' => 'Directeur', 'description' => 'Schoolleiding en evaluatieopvolging', 'permissions' => array_values(array_diff($all, ['roles.manage']))],
            'administration' => ['name' => 'Administratie', 'description' => 'Leerlingen, lesgroepen en rapportering', 'permissions' => ['users.view','students.view','students.manage','groups.view','groups.manage','reports.view','reports.export']],
            'coordinator' => ['name' => 'Vakcoördinator', 'description' => 'Coördinatie en leerdoelen', 'permissions' => ['students.view','groups.view','goals.manage','evaluations.view','evaluations.write','reports.view']],
            'teacher' => ['name' => 'Leerkracht', 'description' => 'Eigen lessen en evaluaties; eigenaarschap wordt per lesgroep gecontroleerd', 'permissions' => ['students.view','groups.view','evaluations.view','evaluations.write','reports.view']],
            'jury' => ['name' => 'Jurylid', 'description' => 'Beoordelingen binnen toegewezen opdrachten', 'permissions' => ['evaluations.view','evaluations.write']],
            'viewer' => ['name' => 'Alleen lezen', 'description' => 'Beperkte inzage', 'permissions' => ['groups.view','reports.view']],
        ];
    }

    public static function ensureFor(School $school): void
    {
        foreach (self::defaults() as $slug => $role) {
            DB::table('school_roles')->insertOrIgnore([
                'school_id' => $school->id,
                'slug' => $slug,
                'name' => $role['name'],
                'description' => $role['description'],
                'permissions' => json_encode($role['permissions']),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
