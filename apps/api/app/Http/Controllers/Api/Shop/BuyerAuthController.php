<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\LoginBuyerRequest;
use App\Http\Requests\Shop\RegisterBuyerRequest;
use App\Models\Buyer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// Storefront buyers use Sanctum bearer tokens (separate from farm users).
class BuyerAuthController extends Controller
{
    public function register(RegisterBuyerRequest $request): JsonResponse
    {
        $buyer = Buyer::create($request->validated());

        return $this->tokenResponse($buyer, 201);
    }

    public function login(LoginBuyerRequest $request): JsonResponse
    {
        $buyer = Buyer::where('email', $request->validated('email'))->first();

        if (! $buyer || ! Hash::check($request->validated('password'), $buyer->password)) {
            throw ValidationException::withMessages(['email' => [__('auth.failed')]]);
        }

        return $this->tokenResponse($buyer);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->only(['id', 'name', 'email', 'phone'])]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    private function tokenResponse(Buyer $buyer, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $buyer->only(['id', 'name', 'email', 'phone']),
            'token' => $buyer->createToken('shop')->plainTextToken,
            'token_type' => 'Bearer',
        ], $status);
    }
}
