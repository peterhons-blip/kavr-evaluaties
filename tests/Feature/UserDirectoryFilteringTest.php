<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDirectoryFilteringTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $code): School
    {
        $school = School::create([
            'name' => 'School '.$code,
            'code' => $code,
            'slug' => strtolower($code),
            'country_code' => 'BE',
            'is_active' => true,
        ]);
        SchoolRoleCatalog::ensureFor($school);

        return $school;
    }

    public function test_platform_filters_school_and_role_on_the_same_membership(): void
    {
        $schoolA = $this->school('A');
        $schoolB = $this->school('B');

        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
        $teacherA = User::factory()->create([
            'first_name' => 'Els',
            'last_name' => 'De Winter',
            'email' => 'els@example.test',
        ]);
        $teacherA->schools()->attach($schoolA->id, ['role' => 'teacher', 'is_active' => true]);
        $teacherA->schools()->attach($schoolB->id, ['role' => 'director', 'is_active' => true]);

        $teacherB = User::factory()->create([
            'first_name' => 'Mira',
            'last_name' => 'Peeters',
            'email' => 'mira@example.test',
        ]);
        $teacherB->schools()->attach($schoolB->id, ['role' => 'teacher', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('platform.users.index', ['school_id' => $schoolA->id, 'role' => 'director']))
            ->assertOk()
            ->assertDontSee('els@example.test')
            ->assertDontSee('mira@example.test');

        $this->get(route('platform.users.index', ['school_id' => $schoolA->id, 'role' => 'teacher']))
            ->assertOk()
            ->assertSee('els@example.test')
            ->assertDontSee('mira@example.test');
    }

    public function test_instant_search_returns_only_results_fragment(): void
    {
        $this->school('A');
        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
        User::factory()->create([
            'first_name' => 'Elise',
            'last_name' => 'Vrancken',
            'email' => 'elise@example.test',
        ]);

        $this->actingAs($admin)->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('platform.users.index', ['q' => 'Elise']))
            ->assertOk()
            ->assertSee('elise@example.test')
            ->assertDontSee('<html', false);
    }

    public function test_environment_selector_shows_only_assigned_active_schools_even_when_one(): void
    {
        $assigned = $this->school('A');
        $other = $this->school('B');
        $user = User::factory()->create(['is_active' => true]);

        $assigned->users()->attach($user->id, ['role' => 'teacher', 'is_active' => true]);

        $this->actingAs($user)
            ->get(route('schools.select'))
            ->assertOk()
            ->assertSee($assigned->name)
            ->assertDontSee($other->name);

        $this->post(route('schools.select.store'), ['school_id' => $other->id])
            ->assertNotFound();
    }
}
