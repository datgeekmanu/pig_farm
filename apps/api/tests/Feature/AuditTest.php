<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    private User $owner;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->owner = User::factory()->create(['account_id' => $this->account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $this->account->id]);
        $this->owner->farms()->attach($this->farm, ['role' => 'owner']);
    }

    public function test_mutation_is_audited(): void
    {
        $this->actingAs($this->owner)->postJson('/api/batches', [
            'nom' => 'Lot Audit', 'date_mise_en_lot' => '2026-03-01', 'effectif_initial' => 10,
        ])->assertStatus(201);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Batch::class,
            'action' => 'created',
            'farm_id' => $this->farm->id,
            'user_id' => $this->owner->id,
        ]);
    }

    public function test_audit_logs_endpoint_owner_only(): void
    {
        Batch::factory()->create(['farm_id' => $this->farm->id]);

        $staff = User::factory()->create(['account_id' => $this->account->id]);
        $staff->farms()->attach($this->farm, ['role' => 'staff']);

        $this->actingAs($staff)->getJson('/api/audit-logs')->assertStatus(403);
        $this->actingAs($this->owner)->getJson('/api/audit-logs')->assertOk();
    }
}
