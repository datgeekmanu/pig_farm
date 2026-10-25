<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Sale;
use App\Support\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BuyerOrderController extends Controller
{
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $order = OrderService::place($request->user(), $request->validated());

        return OrderResource::make($order->load('items', 'payment'))->response()->setStatusCode(201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        return OrderResource::collection(
            $request->user()->orders()->with('items')->latest()->paginate(20)
        );
    }

    public function show(Request $request, Order $order): OrderResource
    {
        $this->authorizeBuyer($request, $order);

        return OrderResource::make($order->load('items', 'payment'));
    }

    // Stub payment: real gateway (mobile-money/card) integrates via webhook later.
    public function pay(Request $request, Order $order): OrderResource
    {
        $this->authorizeBuyer($request, $order);

        DB::transaction(function () use ($order) {
            $order->payment?->update(['status' => 'paid']);
            if ($order->sale_id) {
                Sale::withoutGlobalScopes()->whereKey($order->sale_id)->update(['statut_paiement' => 'paye']);
            }
        });

        return OrderResource::make($order->load('items', 'payment'));
    }

    private function authorizeBuyer(Request $request, Order $order): void
    {
        abort_unless($order->buyer_id === $request->user()->id, 404);
    }
}
