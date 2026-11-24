<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\SchoolSelectionController;
use App\Http\Controllers\MyProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Platform\PermissionMatrixController;
use App\Http\Controllers\Platform\SchoolController;
use App\Http\Controllers\Platform\SchoolLocationController;
use App\Http\Controllers\Platform\SchoolSettingsController;
use App\Http\Controllers\Platform\PlatformSettingsController;
use App\Http\Controllers\Platform\AccessAuditController;
use App\Http\Controllers\Platform\UserController;
use App\Http\Controllers\Platform\Dko3TeacherImportController;
use App\Http\Controllers\Platform\UserBulkController;
use App\Http\Controllers\Platform\SchoolRoleController;
use App\Http\Controllers\Platform\SchoolMemberController;
use App\Http\Controllers\School\MemberController;
use App\Http\Controllers\School\DashboardController;
use App\Http\Controllers\School\SettingsController as SchoolScopedSettingsController;
use App\Http\Controllers\School\LocationController as SchoolScopedLocationController;
use App\Http\Controllers\School\RoleController as SchoolRoleManagementController;


// Startpagina
Route::get('/', function () {
    return redirect()->route('login');
});

// Loginpagina en aanmelden
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');

    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'requestLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');

    Route::get('/s/{slug}/login', [
        LoginController::class,
        'showSchoolLogin'
    ])->name('school.login');
});

// Dashboard: alleen toegankelijk na aanmelden
Route::middleware('auth')->group(function () {
    // Iedere ingelogde gebruiker kan zijn eigen profiel wijzigen,
    // ongeacht schoolrol. Nooit platform- of schoolrechten via dit formulier.
    Route::get('/mijn-profiel', [MyProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/mijn-profiel', [MyProfileController::class, 'update'])->name('profile.update');
    Route::put('/mijn-profiel/wachtwoord', [MyProfileController::class, 'password'])
        ->middleware('throttle:5,1')->name('profile.password');

    Route::get('/dashboard', DashboardController::class)
    ->middleware('active.school')
    ->name('dashboard');

    // Beheer van schoolgebruikers vereist schoollidmaatschap en rechten.
    Route::middleware('active.school')->group(function () {
        // Eigen schoolbeheer, altijd afgeschermd door active.school én school.settings.
        Route::get('/school/instellingen', [SchoolScopedSettingsController::class, 'edit'])->name('school.settings.edit');
        Route::put('/school/instellingen', [SchoolScopedSettingsController::class, 'update'])->name('school.settings.update');
        Route::get('/school/vestigingen', [SchoolScopedLocationController::class, 'index'])->name('school.locations.index');
        Route::get('/school/vestigingen/nieuw', [SchoolScopedLocationController::class, 'create'])->name('school.locations.create');
        Route::post('/school/vestigingen', [SchoolScopedLocationController::class, 'store'])->name('school.locations.store');
        Route::get('/school/vestigingen/{location}/bewerken', [SchoolScopedLocationController::class, 'edit'])->name('school.locations.edit');
        Route::put('/school/vestigingen/{location}', [SchoolScopedLocationController::class, 'update'])->name('school.locations.update');

        Route::get('/school/rollen', [SchoolRoleManagementController::class, 'index'])->name('school.roles.index');
        Route::get('/school/rollen/create', [SchoolRoleManagementController::class, 'create'])->name('school.roles.create');
        Route::post('/school/rollen', [SchoolRoleManagementController::class, 'store'])->name('school.roles.store');
        Route::get('/school/rollen/{role}/edit', [SchoolRoleManagementController::class, 'edit'])->name('school.roles.edit');
        Route::put('/school/rollen/{role}', [SchoolRoleManagementController::class, 'update'])->name('school.roles.update');
        Route::delete('/school/rollen/{role}', [SchoolRoleManagementController::class, 'destroy'])->name('school.roles.destroy');

        // Leerlingen: alleen toegang binnen de actieve school met passende rechten.
        Route::get('/school/leerlingen', [StudentController::class, 'index'])->name('school.students.index');
        Route::get('/school/leerlingen/nieuw', [StudentController::class, 'create'])->name('school.students.create');
        Route::post('/school/leerlingen', [StudentController::class, 'store'])->name('school.students.store');
        Route::get('/school/leerlingen/import', [StudentController::class, 'importForm'])->name('school.students.import');
        Route::post('/school/leerlingen/import/voorbeeld', [StudentController::class, 'preview'])->name('school.students.preview');
        Route::post('/school/leerlingen/import/bevestig', [StudentController::class, 'commit'])->name('school.students.commit');
        Route::get('/school/leerlingen/{student}/bewerken', [StudentController::class, 'edit'])->name('school.students.edit');
        Route::put('/school/leerlingen/{student}', [StudentController::class, 'update'])->name('school.students.update');

        Route::get('/school/gebruikers', [MemberController::class, 'index'])
            ->name('school.members.index');
        Route::post('/school/gebruikers', [MemberController::class, 'store'])
            ->name('school.members.store');
        Route::put('/school/gebruikers/{user}', [MemberController::class, 'update'])
            ->name('school.members.update');
    });

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    Route::get('/schools/select', [
        SchoolSelectionController::class,
        'index'
    ])->name('schools.select');

    Route::post('/schools/select', [
        SchoolSelectionController::class,
        'select'
    ])->name('schools.select.store');

});


// Centraal platformbeheer
Route::middleware(['auth', 'platform.admin'])
    ->prefix('platform')
    ->name('platform.')
    ->group(function () {

        Route::get('/', function () {

            // Algemene platformstatistieken.
            $stats = [
                'schools_total' => \App\Models\School::count(),

                'schools_active' => \App\Models\School::where(
                    'is_active', true
                )->count(),

                'users_total' => \App\Models\User::count(),

                'users_active' => \App\Models\User::where(
                    'is_active', true
                )->count(),

                'locations_total' => \App\Models\SchoolLocation::count(),
            ];

            // De vijf meest recent toegevoegde scholen.
            $recentSchools = \App\Models\School::query()
                ->orderByDesc('is_active')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get();


            return view('platform.dashboard', compact(
                'stats',
                'recentSchools'
            ));

        })->name('dashboard');


        Route::get('/leerlingen', [StudentController::class, 'index'])->name('students.index');
        Route::get('/leerlingen/nieuw', [StudentController::class, 'create'])->name('students.create');
        Route::post('/leerlingen', [StudentController::class, 'store'])->name('students.store');
        Route::get('/leerlingen/import', [StudentController::class, 'importForm'])->name('students.import');
        Route::post('/leerlingen/import/voorbeeld', [StudentController::class, 'preview'])->name('students.preview');
        Route::post('/leerlingen/import/bevestig', [StudentController::class, 'commit'])->name('students.commit');
        Route::get('/leerlingen/{student}/bewerken', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/leerlingen/{student}', [StudentController::class, 'update'])->name('students.update');

        Route::get('/rechten', [PermissionMatrixController::class, 'index'])->name('permissions.index');
        Route::get('/rechten/{school}/gebruikers/{user}', [PermissionMatrixController::class, 'user'])->name('permissions.user');
        Route::put('/rechten/{school}/gebruikers/{user}', [PermissionMatrixController::class, 'updateUser'])->name('permissions.user.update');

        Route::get('/audit', [AccessAuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/mail', [AccessAuditController::class, 'mail'])->name('audit.mail');

        Route::get('/settings', [PlatformSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [PlatformSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-mail', [PlatformSettingsController::class, 'test'])
            ->middleware('throttle:3,1')->name('settings.test-mail');

        // DKO3-personeelimport met controle en definitieve bevestiging.
        Route::get('/users/import', [Dko3TeacherImportController::class, 'create'])->name('users.import');
        Route::post('/users/import/preview', [Dko3TeacherImportController::class, 'preview'])->name('users.import.preview');
        Route::post('/users/import/commit', [Dko3TeacherImportController::class, 'commit'])->name('users.import.commit');
        Route::post('/users/bulk', UserBulkController::class)->middleware('throttle:2,1')->name('users.bulk');

        // Centrale personeelsadministratie.
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/setup-link', [UserController::class, 'sendSetupLink'])->middleware('throttle:3,1')->name('users.setup-link');

        Route::get('/schools/{school}/users', [SchoolMemberController::class, 'index'])->name('schools.users.index');

        Route::get('/schools/{school}/roles', [SchoolRoleController::class, 'index'])->name('schools.roles.index');
        Route::get('/schools/{school}/roles/create', [SchoolRoleController::class, 'create'])->name('schools.roles.create');
        Route::post('/schools/{school}/roles', [SchoolRoleController::class, 'store'])->name('schools.roles.store');
        Route::get('/schools/{school}/roles/{role}/edit', [SchoolRoleController::class, 'edit'])->name('schools.roles.edit');
        Route::put('/schools/{school}/roles/{role}', [SchoolRoleController::class, 'update'])->name('schools.roles.update');
        Route::delete('/schools/{school}/roles/{role}', [SchoolRoleController::class, 'destroy'])->name('schools.roles.destroy');

        Route::get('/schools', [SchoolController::class, 'index'])
            ->name('schools.index');

        Route::get('/schools/create', [SchoolController::class, 'create'])
            ->name('schools.create');

        Route::post('/schools', [SchoolController::class, 'store'])
            ->name('schools.store');

        Route::get('/schools/{school}/edit', [SchoolController::class, 'edit'])
            ->name('schools.edit');

        Route::put('/schools/{school}', [SchoolController::class, 'update'])
            ->name('schools.update');

        Route::delete(
            '/schools/{school}',
            [SchoolController::class, 'destroy']
        )->name('schools.destroy');


        Route::get(
            '/schools/{school}/locations',
            [SchoolLocationController::class, 'index']
        )->name('schools.locations.index');

        Route::get(
            '/schools/{school}/locations/create',
            [SchoolLocationController::class, 'create']
        )->name('schools.locations.create');

        Route::post(
            '/schools/{school}/locations',
            [SchoolLocationController::class, 'store']
        )->name('schools.locations.store');

        Route::get(
            '/schools/{school}/locations/{location}/edit',
            [SchoolLocationController::class, 'edit']
        )->name('schools.locations.edit');

        Route::put(
            '/schools/{school}/locations/{location}',
            [SchoolLocationController::class, 'update']
        )->name('schools.locations.update');

        Route::delete(
            '/schools/{school}/locations/{location}',
            [SchoolLocationController::class, 'destroy']
        )->name('schools.locations.destroy');

        Route::get(
            '/schools/{school}/settings',
            [SchoolSettingsController::class, 'edit']
        )->name('schools.settings.edit');

        Route::put(
            '/schools/{school}/settings',
            [SchoolSettingsController::class, 'update']
        )->name('schools.settings.update');

        Route::get('/schools/{school}/login-preview', function (
            \App\Models\School $school
        ) {
            $school->load('settings');

            return response()
                ->view('auth.login', [
                    'school' => $school,
                ])
                ->header('Cache-Control', 'no-store');
        })->name('schools.login-preview');

    });
