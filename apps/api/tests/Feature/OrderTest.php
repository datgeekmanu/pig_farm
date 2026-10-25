<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Batch;
use App\Models\Buyer;
use App\Models\Farm;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private Buyer $buyer;

    private User $owner;

    private Farm $farm;

    private Batch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->owner = User::factory()->create(['account_id' => $account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $account->id]);
        $this->owner->farms()->attach($this->farm, ['role' => 'owner']);
        $this->batch = Batch::factory()->create(['farm_id' => $this->farm->id, 'effectif_initial' => 10]);
        $this->buyer = Buyer::factory()->create();
    }

    private function product(array $attrs = []): Product
    {
        return Product::factory()->create(array_merge([
            'farm_id' => $this->farm->id, 'batch_id' => $this->batch->id,
            'type' => 'live_pig', 'prix' => 100000, 'stock_qty' => 10, 'is_published' => true,
        ], $attrs));
    }

    public function test_place_order_decrements_stock_and_updates_herd(): void
    {
        $p = $this->product();

        $res = $this->actingAs($this->buyer, 'buyer')->postJson('/api/shop/orders', [
            'items' => [['product_id' => $p->id, 'quantity' => 2]],
            'delivery_address' => 'Lomé',
        ])->assertStatus(201);

        $res->assertJsonPath('data.total', 200000)
            ->assertJsonPath('data.status', 'placed')
            ->assertJsonPath('data.payment.status', 'pending');

        $this->assertEquals(8, $p->fresh()->stock_qty);          // inventory decremented
        $this->assertSame(8, $this->batch->fresh()->currentHead()); // herd reflects the sale (10 - 2)
        $this->assertDatabaseHas('sales', ['farm_id' => $this->farm->id, 'total' => 200000]);
    }

    public function test_place_order_rejects_insufficient_stock(): void
    {
        $p = $this->product(['stock_qty' => 1]);

        $this->actingAs($this->buyer, 'buyer')->postJson('/api/shop/orders', [
            'items' => [['product_id' => $p->id, 'quantity' => 5]],
        ])->assertStatus(422);

        $this->assertEquals(1, $p->fresh()->stock_qty); // unchanged (rolled back)
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_place_order_rejects_products_from_different_farms(): void
    {
        $p1 = $this->product();
        $p2 = Product::factory()->create(['is_published' => true, 'stock_qty' => 5]); // other farm

        $this->actingAs($this->buyer, 'buyer')->postJson('/api/shop/orders', [
            'items' => [['product_id' => $p1->id, 'quantity' => 1], ['product_id' => $p2->id, 'quantity' => 1]],
        ])->assertStatus(422);
    }

    public function test_buyer_history_and_ownership(): void
    {
        $p = $this->product();
        $order = $this->actingAs($this->buyer, 'buyer')->postJson('/api/shop/orders', [
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->json('data.id');

        $this->actingAs($this->buyer, 'buyer')->getJson('/api/shop/orders')->assertOk()->assertJsonCount(1, 'data');

        $other = Buyer::factory()->create();
        $this->actingAs($other, 'buyer')->getJson("/api/shop/orders/{$order}")->assertStatus(404);
    }

    public function test_pay_marks_order_and_sale_paid(): void
    {
        $p = $this->product();
        $order = Order::find($this->actingAs($this->buyer, 'buyer')->postJson('/api/shop/orders', [
            'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->json('data.id'));

        $this->actingAs($this->buyer, 'buyer')->postJson("/api/shop/orders/{$order->id}/pay")
            ->assertOk()->assertJsonPath('data.payment.status', 'paid');

        $this->assertDatabaseHas('sales', ['id' => $order->sale_id, 'statut_paiement' => 'paye']);
    }

    public function test_seller_cancel_restocks_and_voids_sale(): void
    {
        $p = $this->product();
        $order = Order::find($this->actingAs($this->buyer, 'buyer')->postJson('/api/shop/orders', [
            'items' => [['product_id' => $p->id, 'quantity' => 3]],
        ])->json('data.id'));

        $this->assertEquals(7, $p->fresh()->stock_qty);

        $this->actingAs($this->owner, 'web')->patchJson("/api/shop/seller/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertOk()->assertJsonPath('data.status', 'cancelled');

        $this->assertEquals(10, $p->fresh()->stock_qty);         // restocked
        $this->assertDatabaseMissing('sales', ['id' => $order->sale_id]); // sale voided
        $this->assertSame(10, $this->batch->fresh()->currentHead());
    }
}
