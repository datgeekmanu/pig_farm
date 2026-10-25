<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\MortalityRecord;
use App\Models\Pig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MortalityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farm;

    private Batch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $account->id]);
        $this->user->farms()->attach($this->farm, ['role' => 'owner']);
        $this->batch = Batch::factory()->create(['farm_id' => $this->farm->id, 'effectif_initial' => 20]);
    }

    public function test_individual_death_marks_pig_dead_and_derives_batch(): void
    {
        $pig = Pig::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $this->batch->id]);

        $this->actingAs($this->user)->postJson('/api/mortalities', [
            'pig_id' => $pig->id,
            'died_on' => '2026-03-01',
            'cause' => 'maladie',
        ])->assertStatus(201)
            ->assertJsonPath('data.batch_id', $this->batch->id)
            ->assertJsonPath('data.count', 1);

        $this->assertSame('dead', $pig->fresh()->status);
    }

    public function test_batch_loss_records_count(): void
    {
        $this->actingAs($this->user)->postJson('/api/mortalities', [
            'batch_id' => $this->batch->id,
            'died_on' => '2026-03-01',
            'count' => 3,
            'cause' => 'écrasement',
        ])->assertStatus(201)->assertJsonPath('data.count', 3);
    }

    public function test_requires_pig_or_batch(): void
    {
        $this->actingAs($this->user)->postJson('/api/mortalities', ['died_on' => '2026-03-01'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['pig_id', 'batch_id']);
    }

    public function test_cannot_target_foreign_batch(): void
    {
        $foreign = Batch::factory()->create();

        $this->actingAs($this->user)->postJson('/api/mortalities', [
            'batch_id' => $foreign->id,
            'died_on' => '2026-03-01',
        ])->assertJsonValidationErrors('batch_id');
    }

    public function test_stats_compute_rate_per_batch(): void
    {
        MortalityRecord::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $this->batch->id, 'count' => 3]);
        MortalityRecord::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $this->batch->id, 'count' => 1]);

        // 4 deaths / 20 initial = 20%
        $res = $this->actingAs($this->user)->getJson('/api/mortalities/stats')->assertOk();

        $this->assertSame(4, $res->json('data.total_deaths'));
        $this->assertSame(4, $res->json('data.by_batch.0.deaths'));
        $this->assertEquals(20.0, $res->json('data.by_batch.0.rate_pct'));
    }

    public function test_index_filters_by_date(): void
    {
        MortalityRecord::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $this->batch->id, 'died_on' => '2026-01-01']);
        MortalityRecord::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $this->batch->id, 'died_on' => '2026-06-01']);

        $this->actingAs($this->user)->getJson('/api/mortalities?from=2026-05-01')
            ->assertOk()->assertJsonCount(1, 'data');
    }
}
