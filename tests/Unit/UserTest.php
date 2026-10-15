<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\XpLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_automatically_generates_ulid_on_creation(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->ulid);
        $this->assertEquals(26, strlen($user->ulid));
    }

    public function test_user_role_methods(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($superAdmin->isLecturer());

        $lecturer = User::factory()->lecturer()->create();
        $this->assertTrue($lecturer->isLecturer());
        $this->assertFalse($lecturer->isStudent());

        $student = User::factory()->student()->create();
        $this->assertTrue($student->isStudent());
        $this->assertTrue($student->hasRole(UserRole::Student));
    }

    public function test_user_xp_and_level_calculation(): void
    {
        $student = User::factory()->student()->create();

        $this->assertEquals(0, $student->totalXp());
        $this->assertEquals(1, $student->currentLevel());

        XpLedger::create([
            'user_id' => $student->id,
            'amount' => 200,
            'source_type' => 'assignment',
            'description' => 'Test assignment reward',
        ]);

        $student->refresh();
        $this->assertEquals(200, $student->totalXp());
        // sqrt(200 / 50) = sqrt(4) = 2 + 1 = 3
        $this->assertEquals(3, $student->currentLevel());
    }
}
