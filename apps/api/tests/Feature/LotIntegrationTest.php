<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\FeedIntake;
use App\Models\LotWeighing;
use App\Models\StockItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LotIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $account->id]);
        $this->user->farms()->attach($this->farm, ['role' => 'owner']);
    }

    public function test_user_alias_returns_me(): void
    {
        $this->actingAs($this->user)->getJson('/api/user')->assertOk()
            ->assertJsonPath('data.email', $this->user->email);
    }

    public function test_lot_weighing_from_sample_computes_average_and_cv(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id]);

        $res = $this->actingAs($this->user)->postJson("/api/lots/{$batch->id}/weighings", [
            'weights' => [38, 40, 42], 'weighed_on' => '2026-03-01',
        ])->assertStatus(201);

        $this->assertEquals(40, $res->json('data.avg_weight_kg'));
        $this->assertEqualsWithDelta(4.1, (float) $res->json('data.uniformity_cv'), 0.2);
        $this->assertSame(3, $res->json('data.count'));
    }

    public function test_lot_weighing_from_total_and_count(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id]);

        $this->actingAs($this->user)->postJson("/api/lots/{$batch->id}/weighings", [
            'count' => 10, 'total_kg' => 400,
        ])->assertStatus(201)->assertJsonPath('data.avg_weight_kg', '40.00');
    }

    public function test_lot_resource_computes_adg_fcr_and_phase(): void
    {
        $batch = Batch::factory()->create([
            'farm_id' => $this->farm->id, 'effectif_initial' => 10,
            'poids_moyen_initial_kg' => 20, 'date_mise_en_lot' => Carbon::today()->subDays(60),
        ]);
        LotWeighing::factory()->create([
            'farm_id' => $this->farm->id, 'batch_id' => $batch->id,
            'weighed_on' => Carbon::today(), 'count' => 10, 'avg_weight_kg' => 50,
        ]);
        FeedIntake::factory()->create([
            'farm_id' => $this->farm->id, 'batch_id' => $batch->id,
            'date' => Carbon::today(), 'quantity_kg' => 500,
        ]);

        $res = $this->actingAs($this->user)->getJson("/api/lots/{$batch->id}")->assertOk();

        $this->assertSame(10, $res->json('data.headcount'));
        $this->assertSame(500, $res->json('data.adg_g_per_day'));        // (50-20)/60*1000
        $this->assertEquals(1.67, $res->json('data.feed_conversion'));   // 500 / ((50-20)*10)
        $this->assertSame('Croissance', $res->json('data.phase'));       // 50 kg
    }

    public function test_feed_intake_decrements_linked_stock(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id]);
        $feed = StockItem::factory()->create(['farm_id' => $this->farm->id, 'type' => 'alimentation', 'quantite' => 1000]);

        $this->actingAs($this->user)->postJson("/api/lots/{$batch->id}/feed", [
            'date' => '2026-03-01', 'quantity_kg' => 300, 'stock_item_id' => $feed->id,
        ])->assertStatus(201);

        $this->assertEquals(700, $feed->fresh()->quantite);
    }
}
