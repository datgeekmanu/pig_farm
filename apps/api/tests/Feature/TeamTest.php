<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    private User $owner;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->owner = User::factory()->create(['account_id' => $this->account->id, 'email' => 'owner@f.test']);
        $this->farm = Farm::factory()->create(['account_id' => $this->account->id]);
        $this->owner->farms()->attach($this->farm, ['role' => 'owner']);
    }

    public function test_invite_member(): void
    {
        $this->actingAs($this->owner)->postJson('/api/farm/members', [
            'name' => 'Kossi', 'email' => 'kossi@f.test', 'password' => 'password123', 'role' => 'staff',
        ])->assertStatus(201)->assertJsonPath('data.role', 'staff');

        $this->assertDatabaseHas('users', ['email' => 'kossi@f.test', 'account_id' => $this->account->id]);
        $this->assertDatabaseHas('farm_user', ['farm_id' => $this->farm->id, 'role' => 'staff']);
    }

    public function test_invite_rejects_duplicate_email(): void
    {
        $this->actingAs($this->owner)->postJson('/api/farm/members', [
            'name' => 'Dup', 'email' => 'owner@f.test', 'password' => 'password123', 'role' => 'staff',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_staff_cannot_invite(): void
    {
        $staff = User::factory()->create(['account_id' => $this->account->id]);
        $staff->farms()->attach($this->farm, ['role' => 'staff']);

        $this->actingAs($staff)->postJson('/api/farm/members', [
            'name' => 'X', 'email' => 'x@f.test', 'password' => 'password123', 'role' => 'staff',
        ])->assertStatus(403);
    }

    public function test_list_and_remove_member(): void
    {
        $member = User::factory()->create(['account_id' => $this->account->id]);
        $this->farm->users()->attach($member, ['role' => 'staff']);

        $this->actingAs($this->owner)->getJson('/api/farm/members')
            ->assertOk()->assertJsonCount(2, 'data'); // owner + member

        $this->actingAs($this->owner)->deleteJson("/api/farm/members/{$member->id}")->assertOk();
        $this->assertDatabaseMissing('farm_user', ['farm_id' => $this->farm->id, 'user_id' => $member->id]);
    }

    public function test_cannot_remove_self(): void
    {
        $this->actingAs($this->owner)->deleteJson("/api/farm/members/{$this->owner->id}")
            ->assertStatus(422);
    }
}
