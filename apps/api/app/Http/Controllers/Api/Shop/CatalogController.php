<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Public storefront catalog (no auth). Reads published products across farms.
class CatalogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::published()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('search'), fn ($q) => $q->where('nom', 'like', '%'.$request->string('search').'%'))
            ->where('stock_qty', '>', 0);

        return ProductResource::collection($query->latest()->paginate(20));
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_published, 404);

        return ProductResource::make($product);
    }
}
