<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Buyer;
use App\Models\Farm;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::factory()->create();
        $this->owner = User::factory()->create(['account_id' => $account->id]);
        $this->farm = Farm::factory()->create(['account_id' => $account->id]);
        $this->owner->farms()->attach($this->farm, ['role' => 'owner']);
    }

    public function test_public_catalog_shows_only_published_in_stock(): void
    {
        Product::factory()->create(['farm_id' => $this->farm->id, 'is_published' => true, 'stock_qty' => 5]);
        Product::factory()->create(['farm_id' => $this->farm->id, 'is_published' => false]);
        Product::factory()->create(['farm_id' => $this->farm->id, 'is_published' => true, 'stock_qty' => 0]);

        $this->getJson('/api/shop/products')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_buyer_register_and_login(): void
    {
        $this->postJson('/api/shop/register', [
            'name' => 'Ama', 'email' => 'ama@shop.test', 'password' => 'password123', 'phone' => '+22507',
        ])->assertStatus(201)->assertJsonStructure(['data' => ['id', 'email'], 'token']);

        $this->postJson('/api/shop/login', ['email' => 'ama@shop.test', 'password' => 'password123'])
            ->assertOk()->assertJsonStructure(['token']);
    }

    public function test_buyer_me_requires_token(): void
    {
        $this->getJson('/api/shop/me')->assertStatus(401);

        $buyer = Buyer::factory()->create();
        $this->actingAs($buyer, 'buyer')->getJson('/api/shop/me')
            ->assertOk()->assertJsonPath('data.id', $buyer->id);
    }

    public function test_seller_creates_product(): void
    {
        $this->actingAs($this->owner)->postJson('/api/products', [
            'type' => 'pork_cut', 'nom' => 'Côtelettes', 'unit' => 'kg', 'prix' => 2500, 'stock_qty' => 40, 'is_published' => true,
        ])->assertStatus(201)->assertJsonPath('data.nom', 'Côtelettes');

        $this->assertDatabaseHas('products', ['nom' => 'Côtelettes', 'farm_id' => $this->farm->id]);
    }
}
