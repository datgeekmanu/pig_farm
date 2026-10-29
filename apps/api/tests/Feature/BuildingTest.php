<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Building;
use App\Models\Farm;
use App\Models\MortalityRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildingTest extends TestCase
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

    public function test_create_building(): void
    {
        $this->actingAs($this->user)->postJson('/api/buildings', [
            'nom' => 'Bâtiment A', 'surface_m2' => 120,
        ])->assertStatus(201)->assertJsonPath('data.nom', 'Bâtiment A');

        $this->assertDatabaseHas('buildings', ['nom' => 'Bâtiment A', 'farm_id' => $this->farm->id]);
    }

    public function test_density_ok_when_space_sufficient(): void
    {
        $building = Building::factory()->create(['farm_id' => $this->farm->id, 'surface_m2' => 100]);
        Batch::factory()->create(['farm_id' => $this->farm->id, 'building_id' => $building->id, 'effectif_initial' => 10]);

        $res = $this->actingAs($this->user)->getJson("/api/buildings/{$building->id}/density")->assertOk();

        $this->assertSame(10, $res->json('data.current_head'));
        $this->assertEquals(10.0, $res->json('data.m2_per_head'));
        $this->assertSame('ok', $res->json('data.comfort'));
    }

    public function test_density_crowded_and_deaths_reduce_head(): void
    {
        $building = Building::factory()->create(['farm_id' => $this->farm->id, 'surface_m2' => 5]);
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id, 'building_id' => $building->id, 'effectif_initial' => 12]);
        MortalityRecord::factory()->create(['farm_id' => $this->farm->id, 'batch_id' => $batch->id, 'count' => 2]);

        // head = 12 - 2 = 10; 5 m² / 10 = 0.5 m²/head < 0.65 -> crowded
        $res = $this->actingAs($this->user)->getJson("/api/buildings/{$building->id}/density")->assertOk();

        $this->assertSame(10, $res->json('data.current_head'));
        $this->assertEquals(0.5, $res->json('data.m2_per_head'));
        $this->assertSame('crowded', $res->json('data.comfort'));
    }

    public function test_cannot_see_building_from_another_farm(): void
    {
        $foreign = Building::factory()->create();

        $this->actingAs($this->user)->getJson("/api/buildings/{$foreign->id}/density")
            ->assertStatus(404);
    }
}
