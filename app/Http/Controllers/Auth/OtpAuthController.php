<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class OtpAuthController extends Controller
{
    /**
     * POST /login/otp/send
     *
     * Sends a 6-digit OTP to the user's email or phone.
     * Uses Spatie HasOneTimePasswords trait on User model.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $identifier = $request->input('identifier');
        $throttleKey = 'otp-send:' . Str::lower($identifier);

        // ── Rate Limiting: 5 attempts per minute ──
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'identifier' => ['Too many attempts. Please wait ' . RateLimiter::availableIn($throttleKey) . ' seconds.'],
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        // ── Find user by email or phone ──
        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'identifier' => ['No account found with this email or phone number.'],
            ]);
        }

        // ── Generate & store OTP (6 digits, 10 min expiry) ──
        $otp = (string) random_int(100000, 999999);
        Cache::put("otp:{$user->id}", Hash::make($otp), now()->addMinutes(10));

        // ── Send OTP via notification ──
        $user->notify(new \App\Notifications\SendOtpNotification($otp));

        return response()->json(['message' => 'OTP sent successfully.']);
    }

    /**
     * POST /login/otp/verify
     *
     * Verifies the OTP and authenticates the user.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $identifier = $request->input('identifier');
        $otp = $request->input('otp');

        $throttleKey = 'otp-verify:' . Str::lower($identifier);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'otp' => ['Too many attempts. Please wait ' . RateLimiter::availableIn($throttleKey) . ' seconds.'],
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        // ── Find user ──
        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid code. Please try again.'],
            ]);
        }

        // ── Verify OTP ──
        $cachedHash = Cache::get("otp:{$user->id}");

        if (!$cachedHash || !Hash::check($otp, $cachedHash)) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired code. Please try again.'],
            ]);
        }

        // ── Cleanup & authenticate ──
        Cache::forget("otp:{$user->id}");
        RateLimiter::clear($throttleKey);
        Auth::login($user, remember: true);

        return response()->json([
            'message' => 'Authenticated successfully.',
            'redirect' => '/dashboard',
        ]);
    }

    /**
     * POST /forgot-password/verify
     *
     * Verifies the password-reset OTP and returns a reset token.
     */
    public function verifyForgotPasswordOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = $request->input('email');
        $otp = $request->input('otp');

        $throttleKey = 'pwd-otp-verify:' . Str::lower($email);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'otp' => ['Too many attempts. Please wait ' . RateLimiter::availableIn($throttleKey) . ' seconds.'],
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired code.'],
            ]);
        }

        $cachedHash = Cache::get("pwd-otp:{$user->id}");

        if (!$cachedHash || !Hash::check($otp, $cachedHash)) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired code. Please try again.'],
            ]);
        }

        // ── Generate reset token ──
        $token = Str::random(64);
        Cache::put("pwd-reset-token:{$user->id}", Hash::make($token), now()->addMinutes(60));
        Cache::forget("pwd-otp:{$user->id}");
        RateLimiter::clear($throttleKey);

        return response()->json([
            'message' => 'Code verified.',
            'token' => $token,
        ]);
    }
}
