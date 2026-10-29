<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\Sale;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldMiscTest extends TestCase
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

    public function test_upcoming_treatments_with_urgency(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id, 'nom' => 'L-01']);
        Treatment::factory()->create([
            'farm_id' => $this->farm->id, 'batch_id' => $batch->id, 'scope' => 'lot',
            'produit' => 'Vaccin PPC', 'date' => now()->toDateString(), 'date_rappel' => now()->addDay()->toDateString(),
        ]);

        $res = $this->actingAs($this->user)->getJson('/api/treatments/upcoming')->assertOk();

        $this->assertSame(1, $res->json('data.0.inDays'));
        $this->assertSame('danger', $res->json('data.0.urgency'));
        $this->assertSame('Demain', $res->json('data.0.tag'));
        $this->assertSame('L-01', $res->json('data.0.target'));
    }

    public function test_finance_months_param_windows_data(): void
    {
        Sale::factory()->create(['farm_id' => $this->farm->id, 'date_vente' => now()->toDateString(), 'total' => 100000]);
        Sale::factory()->create(['farm_id' => $this->farm->id, 'date_vente' => now()->subMonths(5)->toDateString(), 'total' => 500000]);

        $res = $this->actingAs($this->user)->getJson('/api/finances/overview?months=1')->assertOk();

        $this->assertSame(100000, $res->json('data.chiffre_affaires')); // older sale excluded
    }

    public function test_tasks_done_roundtrip(): void
    {
        $this->actingAs($this->user)->postJson('/api/tasks/done', ['key' => 'weigh-L1'])->assertStatus(201);
        $this->actingAs($this->user)->postJson('/api/tasks/done', ['key' => 'weigh-L1'])->assertStatus(201); // idempotent

        $this->actingAs($this->user)->getJson('/api/tasks/done')->assertOk()
            ->assertJsonFragment(['weigh-L1']);
        $this->assertDatabaseCount('done_tasks', 1);
    }
}
