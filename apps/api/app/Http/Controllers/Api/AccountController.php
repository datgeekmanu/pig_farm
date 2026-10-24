<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ChangePasswordRequest;
use App\Http\Requests\Account\DeleteAccountRequest;
use App\Http\Requests\Account\ResetFarmRequest;
use App\Models\Batch;
use App\Models\Pig;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function updatePassword(ChangePasswordRequest $request): JsonResponse
    {
        // password cast is 'hashed' → stored hashed automatically
        $request->user()->update(['password' => $request->validated('password')]);

        return response()->json(['message' => 'Password updated.']);
    }

    // Wipe the active farm's data, keeping the farm itself. Queries are
    // farm-scoped, so this only touches the current farm. Grows as domain
    // models are added (pigs, batches, …).
    public function resetFarm(ResetFarmRequest $request): JsonResponse
    {
        DB::transaction(function () {
            Pig::query()->delete();
            Batch::query()->delete();
        });

        return response()->json(['message' => 'Farm data reset.']);
    }

    // Deletes the whole account (owner only). Cascades farms + users via FKs.
    public function destroy(DeleteAccountRequest $request): JsonResponse
    {
        $account = $request->user()->account;

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $account->delete();

        return response()->json(['message' => 'Account deleted.']);
    }
}
