<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveMembershipFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_school_membership_is_selected_as_inactive_when_editing(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
        $user = User::factory()->create(['is_active' => true]);
        $school = School::create([
            'name' => 'Academie A',
            'code' => 'ACA',
            'slug' => 'academie-a',
            'country_code' => 'BE',
            'is_active' => true,
        ]);

        SchoolRoleCatalog::ensureFor($school);
        $school->users()->attach($user->id, ['role' => 'teacher', 'is_active' => false]);

        $response = $this->actingAs($admin)->get(route('platform.users.edit', $user));
        $response->assertOk();
        $response->assertSee('memberships['.$school->id.'][is_active]', false);
        $response->assertSee('value="0" selected', false);
        $response->assertDontSee('value="1" selected', false);
    }

    public function test_saving_other_details_does_not_reactivate_an_inactive_membership(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
        $user = User::factory()->create([
            'first_name' => 'Voor',
            'last_name' => 'Naam',
            'is_active' => true,
        ]);
        $school = School::create([
            'name' => 'Academie B',
            'code' => 'ACB',
            'slug' => 'academie-b',
            'country_code' => 'BE',
            'is_active' => true,
        ]);
        SchoolRoleCatalog::ensureFor($school);
        $school->users()->attach($user->id, ['role' => 'teacher', 'is_active' => false]);

        $this->actingAs($admin)->put(route('platform.users.update', $user), [
            'first_name' => 'Andere',
            'last_name' => 'Naam',
            'email' => $user->email,
            'is_active' => '1',
            'is_platform_admin' => '0',
            'memberships' => [
                $school->id => ['role' => 'teacher', 'is_active' => '0'],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect(route('platform.users.index'));

        $member = $user->fresh()->schools()->where('schools.id', $school->id)->firstOrFail();
        $this->assertFalse((bool) $member->pivot->is_active);
        $this->assertSame('Andere', $user->fresh()->first_name);
    }
}
