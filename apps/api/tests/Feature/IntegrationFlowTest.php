<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// End-to-end across modules: herd -> weigh-in -> growth -> sale -> finance.
class IntegrationFlowTest extends TestCase
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

    public function test_herd_to_finance_flow(): void
    {
        $this->actingAs($this->user);

        // 1. create a batch
        $batchId = $this->postJson('/api/batches', [
            'nom' => 'Lot E2E', 'date_mise_en_lot' => '2026-01-01', 'effectif_initial' => 10,
        ])->assertStatus(201)->json('data.id');

        // 2. add a pig to it
        $pigId = $this->postJson('/api/pigs', [
            'identifiant' => 'E2E-1', 'poids_arrivee_kg' => 25, 'batch_id' => $batchId,
            'date_naissance' => '2026-01-01', 'categorie' => 'engraissement',
        ])->assertStatus(201)->json('data.id');

        // 3. weigh it -> appears on the growth curve
        $this->postJson("/api/pigs/{$pigId}/weights", ['weight_kg' => 40, 'weighed_on' => '2026-02-19'])
            ->assertStatus(201);
        $this->getJson("/api/pigs/{$pigId}/growth-curve")->assertOk()
            ->assertJsonPath('actual.0.week', 7);

        // 4. sell from the batch -> head drops + CA recorded
        $this->postJson('/api/sales', [
            'date_vente' => '2026-03-01',
            'items' => [['batch_id' => $batchId, 'quantite' => 2, 'poids_total_kg' => 180, 'prix_kg' => 1800]],
        ])->assertStatus(201)->assertJsonPath('data.total', 324000);

        // 5. finance overview reflects it
        $this->getJson('/api/finances/overview')->assertOk()
            ->assertJsonPath('data.chiffre_affaires', 324000)
            ->assertJsonPath('data.benefice_brut', 324000);

        // 6. batch summary PDF renders
        $this->get("/api/batches/{$batchId}/summary")
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
