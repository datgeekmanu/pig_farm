<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\FarmResource;
use App\Http\Resources\UserResource;
use App\Support\CurrentFarm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // SPA cookie/session login. Client must GET /sanctum/csrf-cookie first.
    public function login(LoginRequest $request): JsonResource
    {
        $data = $request->validated();

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], true)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        return new UserResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResource
    {
        $user = $request->user();

        return (new UserResource($user))->additional([
            'farms' => FarmResource::collection($user->farms),
            'active_farm_id' => app(CurrentFarm::class)->id(),
        ]);
    }
}
