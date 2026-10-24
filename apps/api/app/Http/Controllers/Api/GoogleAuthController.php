<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    // SPA sends a Google access token; we match an existing user by email and
    // start a session. v1 does NOT auto-provision accounts (that's public signup, v2).
    public function login(GoogleLoginRequest $request): JsonResource
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()
                ->userFromToken($request->validated('access_token'));
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'access_token' => ['Invalid Google token.'],
            ]);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            abort(403, 'No account for this Google email. Ask your farm owner to add you.');
        }

        Auth::login($user, true);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return new UserResource($user);
    }
}
