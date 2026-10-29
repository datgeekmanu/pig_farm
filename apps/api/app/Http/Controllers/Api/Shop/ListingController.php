<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreListingRequest;
use App\Http\Resources\ListingResource;
use App\Models\Product;
use App\Support\CurrentFarm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Seller-side lot listings (field app "sell" screen). A listing is a Product
// tied to a lot; publishing exposes it to buyers (the buyer app is parked).
class ListingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ListingResource::collection(
            Product::with('batch')->where('farm_id', $this->farmId())->latest()->get()
        );
    }

    public function store(StoreListingRequest $request): JsonResponse
    {
        $product = Product::create($this->attributes($request) + [
            'farm_id' => $this->farmId(),
            'type' => 'live_pig',
            'unit' => 'kg',
        ]);

        return ListingResource::make($product->load('batch'))->response()->setStatusCode(201);
    }

    public function update(StoreListingRequest $request, Product $product): ListingResource
    {
        abort_unless($product->farm_id === $this->farmId(), 404);
        $product->update($this->attributes($request));

        return ListingResource::make($product->load('batch'));
    }

    public function destroy(Product $product): JsonResponse
    {
        abort_unless($product->farm_id === $this->farmId(), 404);
        $product->delete();

        return response()->json(['message' => 'Listing deleted.']);
    }

    /**
     * Map the frontend listing payload to product columns.
     *
     * @return array<string, mixed>
     */
    private function attributes(StoreListingRequest $request): array
    {
        $data = $request->validated();

        return array_filter([
            'batch_id' => $data['batch_id'] ?? null,
            'nom' => $data['title'] ?? null,
            'category' => $data['category'] ?? null,
            'prix' => $data['priceXofPerKg'] ?? null,
            'market_price' => $data['marketXofPerKg'] ?? null,
            'stock_qty' => $data['quantity'] ?? null,
            'visible' => $data['visible'] ?? null,
            'is_published' => $data['published'] ?? null,
        ], fn ($v) => $v !== null);
    }

    private function farmId(): int
    {
        return app(CurrentFarm::class)->require();
    }
}
