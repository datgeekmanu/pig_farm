<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Support\CurrentFarm;
use App\Support\OrderService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Seller view of incoming storefront orders (farm app, auth:sanctum + farm).
class SellerOrderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return OrderResource::collection(
            Order::where('farm_id', $this->farmId())->with('items')->latest()->paginate(20)
        );
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): OrderResource
    {
        abort_unless($order->farm_id === $this->farmId(), 404);

        $status = $request->validated('status');
        if ($status === 'cancelled') {
            OrderService::cancel($order);
        } else {
            $order->update(['status' => $status]);
        }

        return OrderResource::make($order->fresh('items'));
    }

    private function farmId(): int
    {
        return app(CurrentFarm::class)->require();
    }
}
