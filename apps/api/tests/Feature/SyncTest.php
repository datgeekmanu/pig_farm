<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\Pig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farm;

    private Pig $pig;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $account->id]);
        $this->user->farms()->attach($this->farm, ['role' => 'owner']);
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id]);
        $this->pig = Pig::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $batch->id]);
    }

    public function test_sync_is_idempotent_by_client_uuid(): void
    {
        $uuid = (string) Str::uuid();
        $payload = ['weights' => [['client_uuid' => $uuid, 'pig_id' => $this->pig->id, 'weight_kg' => 30, 'weighed_on' => '2026-03-01']]];

        $this->actingAs($this->user)->postJson('/api/sync', $payload)
            ->assertOk()->assertJsonPath('data.weights.created', 1);

        // replay same batch -> no new record
        $this->actingAs($this->user)->postJson('/api/sync', $payload)
            ->assertOk()->assertJsonPath('data.weights.created', 0)
            ->assertJsonPath('data.weights.duplicates', 1);

        $this->assertDatabaseCount('weight_records', 1);
    }

    public function test_sync_mortality_marks_pig_dead(): void
    {
        $this->actingAs($this->user)->postJson('/api/sync', [
            'mortalities' => [['client_uuid' => (string) Str::uuid(), 'pig_id' => $this->pig->id, 'died_on' => '2026-03-02', 'cause' => 'maladie']],
        ])->assertOk()->assertJsonPath('data.mortalities.created', 1);

        $this->assertSame('dead', $this->pig->fresh()->status);
    }
}
