<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Pig;
use App\Models\Sale;
use App\Support\SaleCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Sale::query()
            ->when($request->filled('from'), fn ($q) => $q->whereDate('date_vente', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('date_vente', '<=', $request->date('to')))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut_paiement', $request->string('statut')));

        return SaleResource::collection($query->with('items')->latest('date_vente')->paginate(20));
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $sale = $this->persist($request->validated());

        return SaleResource::make($sale->load('items'))->response()->setStatusCode(201);
    }

    public function show(Sale $sale): SaleResource
    {
        return SaleResource::make($sale->load('items'));
    }

    public function update(StoreSaleRequest $request, Sale $sale): SaleResource
    {
        $this->persist($request->validated(), $sale);

        return SaleResource::make($sale->fresh('items'));
    }

    public function destroy(Sale $sale): JsonResponse
    {
        $sale->delete();

        return response()->json(['message' => 'Sale deleted.']);
    }

    private function persist(array $data, ?Sale $sale = null): Sale
    {
        return DB::transaction(function () use ($data, $sale) {
            $total = SaleCalculator::total($data['items']);
            $attributes = collect($data)->except('items')->put('total', $total)->all();

            if ($sale) {
                $sale->update($attributes);
                $sale->items()->delete();
            } else {
                $sale = Sale::create($attributes);
            }

            foreach ($data['items'] as $item) {
                $item['line_total'] = SaleCalculator::lineTotal($item);
                $sale->items()->create($item);

                // Mark an individually-sold pig as sold.
                if (! empty($item['pig_id'])) {
                    Pig::whereKey($item['pig_id'])->update(['status' => 'sold']);
                }
            }

            return $sale;
        });
    }
}
