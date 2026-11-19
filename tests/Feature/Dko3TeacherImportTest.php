<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Support\SchoolRoleCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Dko3TeacherImportTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $code): School
    {
        $school = School::create([
            'name' => 'Academie '.$code,
            'code' => $code,
            'slug' => strtolower($code),
            'country_code' => 'BE',
            'is_active' => true,
        ]);
        SchoolRoleCatalog::ensureFor($school);
        return $school;
    }

    public function test_full_dko3_import_is_school_scoped_and_hides_missing_teachers(): void
    {
        $a = $this->school('A');
        $b = $this->school('B');
        $admin = User::factory()->create(['is_platform_admin' => true, 'is_active' => true]);

        $gone = User::factory()->create([
            'first_name' => 'Oude',
            'last_name' => 'Leerkracht',
            'email' => 'gone@example.test',
            'is_active' => true,
        ]);
        $gone->schools()->attach($a->id,['role'=>'teacher','is_active'=>true]);
        $gone->schools()->attach($b->id,['role'=>'teacher','is_active'=>true]);

        $kept = User::factory()->create([
            'first_name'=>'Tess','last_name'=>'Oud',
            'email'=>'tess@example.test',
            'is_active'=>true,
        ]);
        $kept->schools()->attach($a->id,['role'=>'teacher','is_active'=>true]);

        $csv = "voornaam;familienaam;e-mailadres\nTess;Nieuw;tess@example.test\nNoor;Nieuwe;noor@example.test\n";
        $this->actingAs($admin)
            ->post(route('platform.users.import.preview'), [
                'school_id' => $a->id,
                'file' => UploadedFile::fake()->createWithContent('personeel.csv', $csv),
            ])
            ->assertOk()
            ->assertSee('DKO3-import controleren')
            ->assertSee('gone@example.test');

        $token = session('dko3_import_token');
        $this->assertNotEmpty($token);

        $this->post(route('platform.users.import.commit'), [
            'token' => $token,
            'confirm' => '1',
        ])->assertRedirect(route('platform.users.index', ['school_id' => $a->id]));

        $this->assertDatabaseHas('users', ['email'=>'tess@example.test', 'last_name'=>'Nieuw']);
        $this->assertDatabaseHas('users', ['email'=>'noor@example.test']);
        $this->assertDatabaseHas('school_user',[
            'school_id'=>$a->id,'user_id'=>$gone->id,'is_active'=>false
        ]);
        $this->assertDatabaseHas('school_user',[
            'school_id'=>$b->id,'user_id'=>$gone->id,'is_active'=>true
        ]);
        $this->assertTrue($gone->fresh()->is_active);
        $this->assertNotNull(
            \Illuminate\Support\Facades\DB::table('school_user')
                ->where('school_id',$a->id)->where('user_id',$gone->id)
                ->value('dko3_hidden_at')
        );

        $this->get(route('platform.users.index', ['school_id'=>$a->id, 'status'=>'inactive']))
            ->assertOk()->assertDontSee('gone@example.test');

        $this->get(route('platform.users.index',['school_id'=>$b->id]))
            ->assertOk()->assertSee('gone@example.test');
    }

    public function test_invalid_upload_does_not_deactivate_any_accounts(): void
    {
        $school = $this->school('A');
        $admin = User::factory()->create(['is_platform_admin'=>true,'is_active'=>true]);
        $existing = User::factory()->create(['email'=>'existing@example.test']);
        $existing->schools()->attach($school->id,['role'=>'teacher','is_active'=>true]);

        $this->actingAs($admin)
            ->from(route('platform.users.import'))
            ->post(route('platform.users.import.preview'), [
                'school_id'=>$school->id,
                'file'=>UploadedFile::fake()->createWithContent(
                    'teachers.csv', "voornaam;familienaam;e-mailadres\nNaam;Test;not-an-email\n"
                ),
            ])
            ->assertRedirect(route('platform.users.import'))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseHas('school_user',[
            'school_id'=>$school->id,'user_id'=>$existing->id,'is_active'=>true
        ]);
    }

    public function test_bulk_deactivation_will_not_disable_the_current_administrator(): void
    {
        $admin = User::factory()->create(['is_platform_admin'=>true,'is_active'=>true]);
        $this->actingAs($admin)
            ->post(route('platform.users.bulk'), [
                'action'=>'deactivate',
                'user_ids'=>[$admin->id],
            ])
            ->assertSessionHasErrors('user_ids');

        $this->assertTrue($admin->fresh()->is_active);
    }
}
