<?php

namespace Tests\Feature;

use App\Models\PlatformSetting;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolPublicLoginTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $code, bool $active = true): School
    {
        return School::create([
            'name' => 'Academie '.$code,
            'code' => $code,
            'slug' => strtolower($code),
            'country_code' => 'BE',
            'is_active' => $active,
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->is_platform_admin = true;
        $admin->save();

        return $admin;
    }

    private function updateSchool(School $school, bool $defaultLogin, bool $active = true)
    {
        return $this->put(route('platform.schools.update', $school), [
            'name' => $school->name,
            'code' => $school->code,
            'slug' => $school->slug,
            'country_code' => 'BE',
            'is_active' => $active ? '1' : '0',
            'default_login' => $defaultLogin ? '1' : '0',
        ]);
    }

    public function test_school_overview_shows_public_links_and_default_badge(): void
    {
        $school = $this->school('ONE');
        PlatformSetting::create([
            'platform_name' => 'Artelynx',
            'default_login_school_id' => $school->id,
        ]);

        $this->actingAs($this->admin())
            ->get(route('platform.schools.index'))
            ->assertOk()
            ->assertSee(route('school.login', $school->slug))
            ->assertSee('Kopiëren')
            ->assertSee('Openen')
            ->assertSee('Standaard voor /login');
    }

    public function test_switching_default_changes_general_login_school(): void
    {
        $first = $this->school('KAVR');
        $second = $this->school('TWO');

        $this->actingAs($this->admin());
        $this->updateSchool($second, true)->assertRedirect(route('platform.schools.index'));
        $this->assertSame($second->id, (int) PlatformSetting::query()->value('default_login_school_id'));

        auth()->logout();
        $this->get(route('login'))->assertOk()->assertSee('Academie TWO');

        $this->actingAs($this->admin());
        $this->updateSchool($first, true)->assertRedirect(route('platform.schools.index'));
        $this->assertSame($first->id, (int) PlatformSetting::query()->value('default_login_school_id'));
    }

    public function test_inactive_school_cannot_be_selected_as_default(): void
    {
        $school = $this->school('TWO');
        $this->actingAs($this->admin());

        $this->updateSchool($school, true, false)->assertSessionHasErrors('default_login');
        $this->assertNull(PlatformSetting::query()->value('default_login_school_id'));
        $this->assertTrue($school->fresh()->is_active);
    }

    public function test_deactivated_default_falls_back_to_an_active_school(): void
    {
        $default = $this->school('TWO', false);
        $active = $this->school('KAVR');
        PlatformSetting::create([
            'platform_name' => 'Artelynx',
            'default_login_school_id' => $default->id,
        ]);

        $this->get(route('login'))->assertOk()->assertSee('Academie KAVR');
        $this->get(route('school.login', $default->slug))->assertNotFound();
        $this->get(route('school.login', $active->slug))->assertOk();
    }

    public function test_regular_user_cannot_change_default_school(): void
    {
        $school = $this->school('ONE');
        $this->actingAs(User::factory()->create(['is_active' => true]));

        $this->updateSchool($school, true)->assertForbidden();
        $this->assertNull(PlatformSetting::query()->value('default_login_school_id'));
    }

    public function test_disabling_default_school_setting_restores_fallback(): void
    {
        $first = $this->school('KAVR');
        $second = $this->school('TWO');
        PlatformSetting::create([
            'platform_name' => 'Artelynx',
            'default_login_school_id' => $second->id,
        ]);

        $this->actingAs($this->admin());
        $this->updateSchool($second, false)->assertRedirect(route('platform.schools.index'));
        $this->assertNull(PlatformSetting::query()->value('default_login_school_id'));
        auth()->logout();

        $this->get(route('login'))->assertOk()->assertSee('Academie KAVR');
    }
}
