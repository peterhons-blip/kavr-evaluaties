<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolScopedManagementTest extends TestCase
{
    use RefreshDatabase;

    private function academy(string $code): School
    {
        $school = School::create([
            'name' => 'Academie '.$code, 'code' => $code,
            'slug' => strtolower($code), 'country_code' => 'BE', 'is_active' => true,
        ]);
        SchoolRoleCatalog::ensureFor($school);
        return $school;
    }

    private function member(School $school, string $role): User
    {
        $user = User::factory()->create(['is_active' => true, 'is_platform_admin' => false]);
        $user->schools()->attach($school->id, ['role' => $role, 'is_active' => true]);
        return $user;
    }

    private function locationData(string $name): array
    {
        return [
            'name' => $name, 'location_number' => 'V01', 'street' => '',
            'postal_code' => '', 'city' => '', 'email' => '', 'phone' => '',
            'is_main_location' => '1', 'is_active' => '1',
        ];
    }

    public function test_school_admin_can_manage_own_settings_and_locations(): void
    {
        $school = $this->academy('AAA');
        $admin = $this->member($school, 'school_admin');

        $this->actingAs($admin)->withSession(['active_school_id' => $school->id])
            ->get(route('dashboard'))->assertOk()->assertSee('Schoolinstellingen')
            ->assertSee('Vestigingsplaatsen');

        $this->get(route('school.settings.edit'))->assertOk();
        $this->put(route('school.settings.update'), [
            'slogan' => 'Lokaal talent', 'subtitle' => 'Muziek en beeld',
            'login_description' => 'Welkom', 'primary_color' => '#142B45',
            'secondary_color' => '#59AAA5', 'accent_color' => '#E5A65B',
            'language' => 'nl', 'timezone' => 'Europe/Brussels',
        ])->assertRedirect(route('school.settings.edit'));
        $this->assertDatabaseHas('school_settings', [
            'school_id' => $school->id, 'slogan' => 'Lokaal talent',
        ]);

        $this->post(route('school.locations.store'), $this->locationData('Campus A'))
            ->assertRedirect(route('school.locations.index'));
        $this->assertDatabaseHas('school_locations', [
            'school_id' => $school->id, 'name' => 'Campus A',
        ]);
    }

    public function test_teacher_cannot_open_school_management_pages_or_post_changes(): void
    {
        $school = $this->academy('BBB');
        $teacher = $this->member($school, 'teacher');
        $this->actingAs($teacher)->withSession(['active_school_id' => $school->id])
            ->get(route('dashboard'))->assertOk()->assertDontSee('Schoolinstellingen');

        $this->get(route('school.settings.edit'))->assertForbidden();
        $this->get(route('school.locations.index'))->assertForbidden();
        $this->post(route('school.locations.store'), $this->locationData('Verboden'))
            ->assertForbidden();
        $this->assertDatabaseMissing('school_locations', ['name' => 'Verboden']);
    }

    public function test_school_cannot_edit_or_update_location_owned_by_other_academy(): void
    {
        $a = $this->academy('CCC');
        $b = $this->academy('DDD');
        $location = $b->locations()->create([
            'name' => 'Andere academie', 'location_number' => 'V02',
            'is_main_location' => true, 'is_active' => true,
        ]);
        $admin = $this->member($a, 'school_admin');
        $this->actingAs($admin)->withSession(['active_school_id' => $a->id])
            ->get(route('school.locations.edit', $location))->assertNotFound();
        $this->put(route('school.locations.update', $location),
            $this->locationData('Illegale wijziging'))->assertNotFound();
        $this->assertSame('Andere academie', $location->fresh()->name);
    }

    public function test_school_settings_updates_never_change_another_school(): void
    {
        $a = $this->academy('EEE');
        $b = $this->academy('FFF');
        $b->settings()->create(['slogan' => 'Ongewijzigd']);
        $admin = $this->member($a, 'school_admin');
        $this->actingAs($admin)->withSession(['active_school_id' => $a->id])
            ->put(route('school.settings.update'), [
                'slogan' => 'Eigen school', 'primary_color' => '#142B45',
                'secondary_color' => '#59AAA5', 'accent_color' => '#E5A65B',
                'language' => 'nl', 'timezone' => 'Europe/Brussels',
            ])->assertRedirect();
        $this->assertSame('Ongewijzigd', $b->settings()->first()->slogan);
    }
    public function test_school_admin_can_upload_logo_only_to_own_school(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $a = $this->academy('LOGOA');
        $b = $this->academy('LOGOB');
        $b->settings()->create(['slogan' => 'Andere academie']);
        $admin = $this->member($a, 'school_admin');
        // Echte minimale PNG-fixture: onafhankelijk van de optionele GD-extensie.
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'academie.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jVZkAAAAASUVORK5CYII=')
        );

        $this->actingAs($admin)->withSession(['active_school_id' => $a->id])
            ->put(route('school.settings.update'), [
                'slogan' => 'Mijn academie',
                'primary_color' => '#142B45',
                'secondary_color' => '#59AAA5',
                'accent_color' => '#E5A65B',
                'language' => 'nl',
                'timezone' => 'Europe/Brussels',
                'logo' => $file,
            ])->assertRedirect(route('school.settings.edit'));

        $logo = $a->settings()->first()->logo_path;
        $this->assertNotEmpty($logo);
        $this->assertStringStartsWith('school-logos/'.$a->id.'/', $logo);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($logo);
        $this->assertSame('Andere academie', $b->settings()->first()->slogan);
        $this->assertNull($b->settings()->first()->logo_path);
        $this->get(route('dashboard'))->assertOk()->assertSee('Logo van '.$a->name);
    }

    public function test_school_member_filters_do_not_show_users_of_other_schools(): void
    {
        $a = $this->academy('STAFFA');
        $b = $this->academy('STAFFB');
        $admin = $this->member($a, 'school_admin');
        $other = $this->member($b, 'teacher');
        $other->update(['first_name' => 'UniekeAndereAcademie']);
        $this->actingAs($admin)->withSession(['active_school_id' => $a->id])
            ->get(route('school.members.index', ['q' => 'UniekeAndereAcademie']))
            ->assertOk()
            // De zoekterm mag in de inputwaarde terugkomen; geen gebruikersrij mag lekken.
            ->assertSee('Geen personeelsleden gevonden.')
            ->assertDontSee('<strong>UniekeAndereAcademie</strong>', false);
        $this->get(route('school.members.index', ['role' => 'teacher', 'status' => 'active']))
            ->assertOk();
        $this->get(route('school.roles.index'))->assertOk()->assertSee('Rechtenmatrix');
    }

    public function test_school_personnel_list_shows_password_state_and_preserves_separate_columns(): void
    {
        $school = $this->academy('PWSTATE');
        $admin = $this->member($school, 'school_admin');
        $colleague = $this->member($school, 'teacher');
        $colleague->update(['must_change_password' => true, 'password_set_at' => null]);

        $this->actingAs($admin)->withSession(['active_school_id' => $school->id])
            ->get(route('school.members.index', ['password' => 'not_set']))
            ->assertOk()
            ->assertSee('Schoolstatus')
            ->assertSee('Accountstatus')
            ->assertSee('Wachtwoord')
            ->assertSee('Nog in te stellen')
            ->assertSee('Instellink mailen')
            ->assertSee($colleague->email);

        $colleague->update(['must_change_password' => false, 'password_set_at' => now()]);
        $this->get(route('school.members.index', ['password' => 'set']))
            ->assertOk()->assertSee('Ingesteld')->assertSee('Resetlink mailen');
    }

    public function test_school_manager_can_email_password_link_only_to_own_active_member(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $school = $this->academy('PWMAILA');
        $otherSchool = $this->academy('PWMAILB');
        $admin = $this->member($school, 'school_admin');
        $colleague = $this->member($school, 'teacher');
        $otherMember = $this->member($otherSchool, 'teacher');
        $this->actingAs($admin)->withSession(['active_school_id' => $school->id])
            ->post(route('school.members.setup-link', $colleague))
            ->assertRedirect();
        $this->assertDatabaseHas('access_audit_events', [
            'school_id' => $school->id,
            'subject_user_id' => $colleague->id,
            'action' => 'school.member.setup_link_sent',
        ]);
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $colleague,
            \App\Notifications\NederlandseWachtwoordReset::class
        );

        $this->post(route('school.members.setup-link', $otherMember))->assertNotFound();

        $school->users()->updateExistingPivot($colleague->id, ['is_active' => false]);
        $this->post(route('school.members.setup-link', $colleague))
            ->assertSessionHasErrors('email');
    }

    public function test_teacher_without_manage_users_cannot_email_password_links(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $school = $this->academy('PWROLE');
        $teacher = $this->member($school, 'teacher');
        $colleague = $this->member($school, 'teacher');
        $this->actingAs($teacher)->withSession(['active_school_id' => $school->id])
            ->post(route('school.members.setup-link', $colleague))->assertForbidden();
        \Illuminate\Support\Facades\Notification::assertNothingSent();
    }

    public function test_school_admin_can_edit_staff_profile_and_school_role(): void
    {
        $school = $this->academy('PERSONA');
        $admin = $this->member($school, 'school_admin');
        $teacher = $this->member($school, 'teacher');
        $this->actingAs($admin)->withSession(['active_school_id' => $school->id]);
        $this->get(route('school.members.index'))->assertOk()
            ->assertSee('Bewerken')
            ->assertDontSee('<div class="sm-summary">', false);
        $this->get(route('school.members.edit', $teacher))->assertOk()
            ->assertSee('Persoons- en contactgegevens');
        $this->put(route('school.members.details.update', $teacher), [
            'first_name' => 'Testvoornaam',
            'last_name' => 'Testfamilienaam',
            'email' => 'schoolperson@example.test',
            'phone' => '012345678',
            'role' => 'teacher',
            'is_active' => '1',
        ])->assertRedirect(route('school.members.edit', $teacher));
        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'first_name' => 'Testvoornaam',
            'email' => 'schoolperson@example.test',
            'phone' => '012345678',
        ]);
    }

    public function test_school_admin_cannot_edit_unlinked_staff_member(): void
    {
        $a = $this->academy('EDTA');
        $b = $this->academy('EDTB');
        $admin = $this->member($a, 'school_admin');
        $other = $this->member($b, 'teacher');
        $this->actingAs($admin)->withSession(['active_school_id' => $a->id])
            ->get(route('school.members.edit', $other))->assertNotFound();
        $this->put(route('school.members.details.update', $other), [
            'first_name' => 'NotAllowed',
            'last_name' => 'Test',
            'email' => 'no@example.test',
            'role' => 'teacher',
            'is_active' => '1',
        ])->assertNotFound();
        $this->assertNotSame('NotAllowed', $other->fresh()->first_name);
    }

    public function test_shared_staff_account_requires_confirmation_before_profile_changes(): void
    {
        $a = $this->academy('SHRA');
        $b = $this->academy('SHRB');
        $admin = $this->member($a, 'school_admin');
        $teacher = $this->member($a, 'teacher');
        $teacher->schools()->attach($b->id, ['role' => 'teacher', 'is_active' => true]);

        $this->actingAs($admin)->withSession(['active_school_id' => $a->id])
            ->put(route('school.members.details.update', $teacher), [
                'first_name' => 'NewName',
                'last_name' => $teacher->last_name,
                'email' => $teacher->email,
                'phone' => $teacher->phone,
                'role' => 'teacher',
                'is_active' => '1',
            ])->assertSessionHasErrors('confirm_shared_profile');
        $this->assertNotSame('NewName', $teacher->fresh()->first_name);
    }

}
