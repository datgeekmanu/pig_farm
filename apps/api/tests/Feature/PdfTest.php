<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\Formula;
use App\Models\Ingredient;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->user = User::factory()->create(['account_id' => $account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $account->id, 'name' => 'Ferme Test', 'payment_info' => 'Mobile Money: +225 07']);
        $this->user->farms()->attach($this->farm, ['role' => 'owner']);
    }

    private function assertPdf($response): void
    {
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_sale_receipt_pdf(): void
    {
        $sale = Sale::factory()->create(['farm_id' => $this->farm->id, 'total' => 150000]);
        $sale->items()->create(['type_porc' => 'Charcutier', 'quantite' => 2, 'poids_total_kg' => 100, 'prix_kg' => 1500, 'line_total' => 150000]);

        $this->assertPdf($this->actingAs($this->user)->get("/api/sales/{$sale->id}/receipt"));
    }

    public function test_batch_summary_pdf(): void
    {
        $batch = Batch::factory()->create(['farm_id' => $this->farm->id]);

        $this->assertPdf($this->actingAs($this->user)->get("/api/batches/{$batch->id}/summary"));
    }

    public function test_formula_sheet_pdf(): void
    {
        $formula = Formula::factory()->create(['farm_id' => $this->farm->id]);
        $ingredient = Ingredient::factory()->create(['farm_id' => $this->farm->id]);
        $formula->lines()->create(['ingredient_id' => $ingredient->id, 'percent' => 100]);

        $this->assertPdf($this->actingAs($this->user)->get("/api/formulas/{$formula->id}/sheet"));
    }

    public function test_finance_report_pdf(): void
    {
        $this->assertPdf($this->actingAs($this->user)->get('/api/finances/report'));
    }

    public function test_gtt_report_pdf(): void
    {
        $this->assertPdf($this->actingAs($this->user)->get('/api/reproduction/gtt-report'));
    }

    public function test_foreign_sale_receipt_is_404(): void
    {
        $foreign = Sale::factory()->create();

        $this->actingAs($this->user)->get("/api/sales/{$foreign->id}/receipt")->assertStatus(404);
    }
}
