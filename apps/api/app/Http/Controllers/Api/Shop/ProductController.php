<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreProductRequest;
use App\Http\Requests\Shop\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\CurrentFarm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Seller product management (farm app, auth:sanctum + farm). Products aren't
// globally farm-scoped, so we filter/guard by the active farm here.
class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::where('farm_id', $this->farmId())->latest()->paginate(20)
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated() + ['farm_id' => $this->farmId()]);

        return ProductResource::make($product)->response()->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        $this->authorizeFarm($product);

        return ProductResource::make($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $this->authorizeFarm($product);
        $product->update($request->validated());

        return ProductResource::make($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorizeFarm($product);
        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    private function farmId(): int
    {
        return app(CurrentFarm::class)->require();
    }

    private function authorizeFarm(Product $product): void
    {
        abort_unless($product->farm_id === $this->farmId(), 404);
    }
}
