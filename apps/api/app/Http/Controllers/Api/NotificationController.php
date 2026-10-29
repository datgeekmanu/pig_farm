<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\FarmNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FarmNotification::query()
            ->when($request->boolean('unread'), fn ($q) => $q->unread())
            ->latest();

        return NotificationResource::collection($query->paginate(30))
            ->additional(['unread_count' => FarmNotification::unread()->count()])
            ->response();
    }

    public function read(FarmNotification $notification): JsonResponse
    {
        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read.']);
    }

    public function readAll(): JsonResponse
    {
        FarmNotification::unread()->update(['read_at' => now()]);

        return response()->json(['message' => 'All marked as read.']);
    }
}
