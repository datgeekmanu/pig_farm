<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Breeding;
use App\Models\Farm;
use App\Models\Farrowing;
use App\Models\Pig;
use App\Models\User;
use App\Models\Weaning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReproductionTest extends TestCase
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

    private function sow(): Pig
    {
        return Pig::factory()->create(['farm_id' => $this->farm->id, 'categorie' => 'truie', 'sexe' => 'female']);
    }

    public function test_breeding_predicts_farrowing_date(): void
    {
        $sow = $this->sow();

        $this->actingAs($this->user)->postJson('/api/breedings', [
            'sow_id' => $sow->id, 'bred_on' => '2026-01-01',
        ])->assertStatus(201)
            ->assertJsonPath('data.expected_farrow_on', '2026-04-25') // +114 days
            ->assertJsonPath('data.status', 'saillie');
    }

    public function test_ultrasound_result_sets_status(): void
    {
        $breeding = Breeding::factory()->create(['farm_id' => $this->farm->id, 'sow_id' => $this->sow()->id]);

        $this->actingAs($this->user)->putJson("/api/breedings/{$breeding->id}", [
            'ultrasound_on' => '2026-02-01', 'ultrasound_result' => 'gestante',
        ])->assertOk()->assertJsonPath('data.status', 'gestante');
    }

    public function test_farrowing_inherits_sow_and_closes_breeding(): void
    {
        $sow = $this->sow();
        $breeding = Breeding::factory()->create(['farm_id' => $this->farm->id, 'sow_id' => $sow->id]);

        $this->actingAs($this->user)->postJson('/api/farrowings', [
            'breeding_id' => $breeding->id, 'farrowed_on' => '2026-04-25', 'born_alive' => 11, 'born_dead' => 1,
        ])->assertStatus(201)->assertJsonPath('data.sow_id', $sow->id);

        $this->assertSame('mise_bas', $breeding->fresh()->status);
    }

    public function test_weaning_record(): void
    {
        $farrowing = Farrowing::factory()->create(['farm_id' => $this->farm->id, 'sow_id' => $this->sow()->id]);

        $this->actingAs($this->user)->postJson('/api/weanings', [
            'farrowing_id' => $farrowing->id, 'weaned_on' => '2026-05-23', 'weaned_count' => 9,
        ])->assertStatus(201)->assertJsonPath('data.weaned_count', 9);
    }

    public function test_empty_sows_excludes_active_breedings(): void
    {
        $free = $this->sow();
        $served = $this->sow();
        Breeding::factory()->create(['farm_id' => $this->farm->id, 'sow_id' => $served->id, 'status' => 'gestante']);
        Pig::factory()->create(['farm_id' => $this->farm->id, 'categorie' => 'engraissement']); // not a sow

        $this->actingAs($this->user)->getJson('/api/reproduction/empty-sows')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $free->id);
    }

    public function test_gtt_computes_indicators(): void
    {
        $sow = $this->sow();
        Breeding::factory()->count(2)->create(['farm_id' => $this->farm->id, 'sow_id' => $sow->id]);
        $farrowing = Farrowing::factory()->create(['farm_id' => $this->farm->id, 'sow_id' => $sow->id, 'born_alive' => 10, 'born_dead' => 1]);
        Weaning::factory()->create(['farm_id' => $this->farm->id, 'farrowing_id' => $farrowing->id, 'weaned_count' => 8]);

        $res = $this->actingAs($this->user)->getJson('/api/reproduction/gtt')->assertOk();

        $this->assertSame(2, $res->json('data.nb_saillies'));
        $this->assertSame(1, $res->json('data.nb_mises_bas'));
        $this->assertEquals(50.0, $res->json('data.taux_mise_bas_pct'));
        $this->assertEquals(10, $res->json('data.nes_vivants_moyen'));
        $this->assertEquals(20.0, $res->json('data.mortalite_allaitement_pct')); // (10-8)/10
    }

    public function test_cannot_access_foreign_breeding(): void
    {
        $foreign = Breeding::factory()->create();

        $this->actingAs($this->user)->getJson("/api/breedings/{$foreign->id}")->assertStatus(404);
    }
}
