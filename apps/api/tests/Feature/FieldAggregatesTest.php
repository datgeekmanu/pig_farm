<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Breeding;
use App\Models\Farm;
use App\Models\Farrowing;
use App\Models\LotWeighing;
use App\Models\MortalityRecord;
use App\Models\Pig;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldAggregatesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $account->id, 'name' => 'Aminata Ndongo']);
        $this->farm = Farm::factory()->create(['account_id' => $account->id]);
        $this->user->farms()->attach($this->farm, ['role' => 'owner']);
    }

    public function test_lot_events_timeline_merges_sources(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id]);
        LotWeighing::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $batch->id, 'weighed_on' => '2026-02-01', 'avg_weight_kg' => 45]);
        MortalityRecord::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $batch->id, 'died_on' => '2026-03-01', 'count' => 2]);
        Treatment::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $batch->id, 'scope' => 'lot', 'date' => '2026-02-15', 'produit' => 'Ivermectine']);

        $res = $this->actingAs($this->user)->getJson("/api/lots/{$batch->id}/events")->assertOk();

        $this->assertCount(3, $res->json('data'));
        $this->assertSame('2026-03-01', $res->json('data.0.date')); // newest first
    }

    public function test_sows_projection_status(): void
    {
        $gestante = Pig::factory()->create(['farm_id' => $this->farm->id, 'categorie' => 'truie']);
        Breeding::factory()->create(['farm_id' => $this->farm->id, 'sow_id' => $gestante->id, 'status' => 'gestante']);

        $lactating = Pig::factory()->create(['farm_id' => $this->farm->id, 'categorie' => 'truie']);
        Farrowing::factory()->create(['farm_id' => $this->farm->id, 'sow_id' => $lactating->id, 'farrowed_on' => now()->subDays(5), 'born_alive' => 11]);

        $res = $this->actingAs($this->user)->getJson('/api/sows')->assertOk();

        $byId = collect($res->json('data'))->keyBy('id');
        $this->assertSame('gestante', $byId[$gestante->id]['status']);
        $this->assertSame('allaitante', $byId[$lactating->id]['status']);
        $this->assertSame(1, $byId[$lactating->id]['parity']);
    }

    public function test_farm_summary(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id, 'effectif_initial' => 20]);
        MortalityRecord::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $batch->id, 'count' => 2]);

        $res = $this->actingAs($this->user)->getJson('/api/farm/summary')->assertOk();

        $this->assertSame(18, $res->json('data.herd.total'));       // 20 - 2
        $this->assertEquals(10.0, $res->json('data.herd.mortalityPct')); // 2/20
        $this->assertSame('Aminata', $res->json('data.user.firstName'));
    }
}
