<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoneTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Completion state for the client-generated "À faire aujourd'hui" tasks.
class TaskController extends Controller
{
    public function done(): JsonResponse
    {
        $keys = DoneTask::whereDate('done_on', now()->toDateString())->pluck('task_key');

        return response()->json(['data' => $keys]);
    }

    public function markDone(Request $request): JsonResponse
    {
        $key = $request->validate(['key' => ['required', 'string', 'max:255']])['key'];
        $today = now()->toDateString();

        if (! DoneTask::where('task_key', $key)->whereDate('done_on', $today)->exists()) {
            DoneTask::create(['task_key' => $key, 'done_on' => $today]);
        }

        return response()->json(['message' => 'Task marked done.'], 201);
    }
}
