<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ChangePasswordRequest;
use App\Http\Requests\Account\DeleteAccountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function updatePassword(ChangePasswordRequest $request): JsonResponse
    {
        // password cast is 'hashed' → stored hashed automatically
        $request->user()->update(['password' => $request->validated('password')]);

        return response()->json(['message' => 'Password updated.']);
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
