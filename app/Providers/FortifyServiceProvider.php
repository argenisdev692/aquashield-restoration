<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use App\Notifications\SuspiciousLoginAttemptNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // ── Inertia Views ──────────────────────────────────────────
        Fortify::loginView(fn() => Inertia::render('auth/LoginPage'));

        Fortify::requestPasswordResetLinkView(fn() => Inertia::render('auth/ForgotPasswordPage'));

        Fortify::resetPasswordView(fn(Request $request) => Inertia::render('auth/ForgotPasswordPage', [
            'token' => $request->route('token'),
            'email' => $request->query('email'),
        ]));

        // ── Rate Limiting ──────────────────────────────────────────
        $this->configureRateLimiters();
    }

    /**
     * Configure Fortify's rate limiters with suspicious login detection.
     *
     * ── Login Limiter ──
     * • 5 attempts per minute per email+IP.
     * • On 429 response: dispatches a queued alert notification
     *   (via Horizon) to the target account email.
     * • Alert emails are throttled to 1 per 15 minutes per email.
     *
     * ── Two-Factor Limiter ──
     * • 5 attempts per minute per session.
     */
    private function configureRateLimiters(): void
    {
        // ── Login rate limiter with suspicious activity alert ──
        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input(Fortify::username()));
            $ip = $request->ip() ?? 'unknown';
            $throttleKey = Str::transliterate($email . '|' . $ip);

            return Limit::perMinute(5)
                ->by($throttleKey)
                ->response(function (Request $req, array $headers) use ($email, $ip): void {
                    // ── Dispatch suspicious login alert ──
                    $this->dispatchSuspiciousLoginAlert($req, $email, $ip);
                });
        });

        // ── Two-Factor rate limiter ──
        RateLimiter::for('two-factor', function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                (string) $request->session()->get('login.id'),
            );
        });
    }

    /**
     * Dispatch a queued suspicious login alert to the target account.
     * Throttled to max 1 alert per 15 minutes per email.
     */
    private function dispatchSuspiciousLoginAlert(Request $request, string $email, string $ip): void
    {
        $alertCacheKey = 'fortify-alert:' . $email;

        if (Cache::has($alertCacheKey)) {
            return; // ── Already alerted recently ──
        }

        $user = User::where('email', $email)->first();

        if ($user === null) {
            return;
        }

        $user->notify(new SuspiciousLoginAttemptNotification(
            ipAddress: $ip,
            userAgent: $request->userAgent() ?? 'unknown',
            attemptedAt: now()->toDateTimeString(),
            route: $request->path(),
        ));

        // 15-minute cooldown
        Cache::put($alertCacheKey, true, 900);

        Log::warning('Fortify: Suspicious login alert dispatched', [
            'email' => $email,
            'ip' => $ip,
        ]);
    }
}
