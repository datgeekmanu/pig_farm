<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    // Emails a reset link (points to the frontend; see AppServiceProvider).
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->validated());

        if ($status !== Password::ResetLinkSent) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return response()->json(['message' => __($status)]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->validated(),
            function ($user, string $password) {
                $user->forceFill(['password' => $password])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return response()->json(['message' => __($status)]);
    }
}
