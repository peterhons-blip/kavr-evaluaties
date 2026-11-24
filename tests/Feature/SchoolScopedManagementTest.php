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
}
