<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\StoreMovementRequest;
use App\Http\Requests\Stock\StoreStockItemRequest;
use App\Http\Requests\Stock\UpdateStockItemRequest;
use App\Http\Resources\StockItemResource;
use App\Http\Resources\StockMovementResource;
use App\Models\StockItem;
use App\Support\StockLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = StockItem::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('search'), fn ($q) => $q->where('nom', 'like', '%'.$request->string('search').'%'))
            ->when($request->boolean('low'), fn ($q) => $q->whereNotNull('seuil_alerte')->whereColumn('quantite', '<=', 'seuil_alerte'));

        return StockItemResource::collection($query->orderBy('nom')->paginate(20));
    }

    public function store(StoreStockItemRequest $request): JsonResponse
    {
        $item = StockItem::create($request->validated());

        return StockItemResource::make($item)->response()->setStatusCode(201);
    }

    public function show(StockItem $stockItem): StockItemResource
    {
        return StockItemResource::make($stockItem);
    }

    public function update(UpdateStockItemRequest $request, StockItem $stockItem): StockItemResource
    {
        $stockItem->update($request->validated());

        return StockItemResource::make($stockItem);
    }

    public function destroy(StockItem $stockItem): JsonResponse
    {
        $stockItem->delete();

        return response()->json(['message' => 'Stock item deleted.']);
    }

    public function movements(StockItem $stockItem): AnonymousResourceCollection
    {
        return StockMovementResource::collection($stockItem->movements()->latest()->paginate(30));
    }

    public function storeMovement(StoreMovementRequest $request, StockItem $stockItem): JsonResponse
    {
        try {
            $movement = DB::transaction(fn () => StockLedger::apply(
                $stockItem,
                (float) $request->validated('change'),
                $request->validated('reason') ?? 'ajustement',
                $request->validated('notes'),
            ));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return StockMovementResource::make($movement)->response()->setStatusCode(201);
    }
}
