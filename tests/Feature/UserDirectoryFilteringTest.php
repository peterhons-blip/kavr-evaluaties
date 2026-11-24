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

    public function test_platform_directory_paginates_in_dutch_and_sorts_highest_role_first(): void
    {
        $school = $this->school('A');
        $admin = User::factory()->create([
            'is_platform_admin' => true, 'is_active' => true,
            'first_name' => 'Admin', 'last_name' => 'Zebra',
            'email' => 'admin@example.test',
        ]);

        foreach (range(1, 23) as $i) {
            $user = User::factory()->create([
                'first_name' => 'Leraar',
                'last_name' => 'Teacher'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'email' => 'teacher'.$i.'@example.test',
            ]);
            $user->schools()->attach($school->id, ['role' => 'teacher', 'is_active' => true]);
        }
        $director = User::factory()->create([
            'first_name' => 'Directeur', 'last_name' => 'Zulu',
            'email' => 'director@example.test',
        ]);
        $director->schools()->attach($school->id, ['role' => 'director', 'is_active' => true]);

        $first = $this->actingAs($admin)->get(route('platform.users.index', ['school_id' => $school->id]));
        $first->assertOk()->assertSee('Volgende')->assertSee('pagina 2')->assertSee('director@example.test');
        $this->assertLessThan(
            strpos($first->getContent(), 'teacher1@example.test'),
            strpos($first->getContent(), 'director@example.test')
        );

        $second = $this->get(route('platform.users.index', [
            'school_id' => $school->id, 'page' => 2,
        ]));
        $second->assertOk()->assertSee('Vorige')->assertSee('Resultaten');
        $second->assertDontSee('pagination.next')->assertDontSee('Showing');
        $second->assertDontSee('director@example.test');
    }

    public function test_school_directory_sorts_administration_above_teachers(): void
    {
        $school = $this->school('B');
        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);
        $teacher = User::factory()->create([
            'last_name' => 'Aaa', 'first_name' => 'Teacher',
            'email' => 'teacher-school@example.test',
        ]);
        $staff = User::factory()->create([
            'last_name' => 'Zzz', 'first_name' => 'Administration',
            'email' => 'admin-school@example.test',
        ]);
        $teacher->schools()->attach($school->id, ['role' => 'teacher', 'is_active' => true]);
        $staff->schools()->attach($school->id, ['role' => 'administration', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('platform.schools.users.index', $school));
        $response->assertOk();
        $this->assertLessThan(
            strpos($response->getContent(), 'teacher-school@example.test'),
            strpos($response->getContent(), 'admin-school@example.test')
        );
    }

    public function test_duplicate_personnel_email_shows_clear_dutch_error_and_search_link(): void
    {
        $school = $this->school('A');
        $admin = User::factory()->create([
            'is_platform_admin' => true,
            'is_active' => true,
        ]);
        User::factory()->create([
            'email' => 'tom.bronckaers@example.test',
            'first_name' => 'Tom',
            'last_name' => 'Bronckaers',
        ]);

        $this->actingAs($admin)
            ->from(route('platform.users.create'))
            ->post(route('platform.users.store'), [
                'first_name' => 'Tom',
                'last_name' => 'Bronckaers',
                'email' => 'tom.bronckaers@example.test',
                'is_active' => '1',
                'is_platform_admin' => '1',
                'memberships' => [
                    $school->id => ['role' => 'school_admin', 'is_active' => '1'],
                ],
            ])
            ->assertRedirect(route('platform.users.create'))
            ->assertSessionHasErrors([
                'email' => 'Dit e-mailadres is al gekoppeld aan een bestaand personeelsaccount. Zoek het personeelslid op en bewerk de bestaande account in plaats van een nieuwe aan te maken.',
            ]);

        $this->get(route('platform.users.create'))
            ->assertOk()
            ->assertSee('Zoek het bestaande personeelsaccount op')
            ->assertSee('tom.bronckaers@example.test');
        $this->assertSame(1, User::where('email', 'tom.bronckaers@example.test')->count());
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
