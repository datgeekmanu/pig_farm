<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\Pig;
use App\Models\User;
use App\Models\WeightRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightTest extends TestCase
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
        $this->batch = Batch::factory()->create(['farm_id' => $this->farm->id]);
    }

    private function pig(array $attrs = []): Pig
    {
        return Pig::factory()->create([
            'farm_id' => $this->farm->id,
            'batch_id' => $this->batch->id,
            ...$attrs,
        ]);
    }

    public function test_store_weight_denormalises_batch_id(): void
    {
        $pig = $this->pig();

        $this->actingAs($this->user)->postJson("/api/pigs/{$pig->id}/weights", [
            'weight_kg' => 30.5,
        ])->assertStatus(201)->assertJsonPath('data.weight_kg', '30.50');

        $this->assertDatabaseHas('weight_records', [
            'pig_id' => $pig->id,
            'batch_id' => $this->batch->id,
            'farm_id' => $this->farm->id,
        ]);
    }

    public function test_cannot_weigh_pig_from_another_farm(): void
    {
        $foreign = Pig::factory()->create();

        $this->actingAs($this->user)->postJson("/api/pigs/{$foreign->id}/weights", [
            'weight_kg' => 30,
        ])->assertStatus(404);
    }

    public function test_batch_weight_stats_use_latest_weight_per_pig(): void
    {
        // two pigs; each with an older + newer weigh-in -> latest used
        $p1 = $this->pig();
        $p2 = $this->pig();
        WeightRecord::factory()->create(['farm_id' => $this->farm->id, 'pig_id' => $p1->id, 'batch_id' => $this->batch->id, 'weight_kg' => 50, 'weighed_on' => '2026-01-01']);
        WeightRecord::factory()->create(['farm_id' => $this->farm->id, 'pig_id' => $p1->id, 'batch_id' => $this->batch->id, 'weight_kg' => 8, 'weighed_on' => '2026-02-01']);
        WeightRecord::factory()->create(['farm_id' => $this->farm->id, 'pig_id' => $p2->id, 'batch_id' => $this->batch->id, 'weight_kg' => 99, 'weighed_on' => '2026-01-01']);
        WeightRecord::factory()->create(['farm_id' => $this->farm->id, 'pig_id' => $p2->id, 'batch_id' => $this->batch->id, 'weight_kg' => 12, 'weighed_on' => '2026-02-01']);

        // latest weights: p1=8, p2=12 -> mean 10, std 2, cv 20%
        $res = $this->actingAs($this->user)->getJson("/api/batches/{$this->batch->id}/weight-stats")->assertOk();

        $this->assertSame(2, $res->json('data.count'));
        $this->assertEquals(10.0, $res->json('data.mean'));
        $this->assertEquals(20.0, $res->json('data.cv_pct'));
    }

    public function test_pig_growth_curve_maps_weighins_to_age_weeks(): void
    {
        $pig = $this->pig(['date_naissance' => '2026-01-01', 'date_arrivee' => null]);
        // weigh-in 14 days after birth -> week 2
        WeightRecord::factory()->create(['farm_id' => $this->farm->id, 'pig_id' => $pig->id, 'batch_id' => $this->batch->id, 'weight_kg' => 5, 'weighed_on' => '2026-01-15']);

        $res = $this->actingAs($this->user)->getJson("/api/pigs/{$pig->id}/growth-curve")->assertOk();

        $res->assertJsonPath('actual.0.week', 2);
        $this->assertEquals(5.0, $res->json('actual.0.weight_kg'));
        $this->assertNotEmpty($res->json('reference'));
        $this->assertArrayHasKey('sevrage', $res->json('phases'));
    }

    public function test_batch_growth_curve_returns_reference_and_actual(): void
    {
        $pig = $this->pig(['date_naissance' => '2026-01-01']);
        WeightRecord::factory()->create(['farm_id' => $this->farm->id, 'pig_id' => $pig->id, 'batch_id' => $this->batch->id, 'weight_kg' => 6, 'weighed_on' => '2026-01-22']);

        $res = $this->actingAs($this->user)->getJson("/api/batches/{$this->batch->id}/growth-curve")
            ->assertOk()
            ->assertJsonPath('actual.0.week', 3);

        $this->assertEquals(6.0, $res->json('actual.0.weight_kg'));
    }
}
