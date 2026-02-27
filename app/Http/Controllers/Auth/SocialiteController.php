<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialiteProvider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * SocialiteController
 *
 * Handles OAuth redirect/callback for social login providers.
 *
 * ── Flow ─────────────────────────────────────────────────────
 * 1. User clicks "Sign in with Google"
 * 2. redirect() → Socialite redirects to Google consent screen
 * 3. Google calls back → callback() receives the OAuth user data
 * 4. First-time user: creates User + SocialiteProvider records
 *    Existing user: links the provider or updates tokens
 * 5. Authenticates and redirects to /dashboard
 *
 * ── Supported Providers ──────────────────────────────────────
 * Currently: google
 * Extensible to: github, facebook, linkedin-openid, etc.
 * Add config in config/services.php + add route for each.
 */
final class SocialiteController extends Controller
{
    /**
     * Allowed OAuth providers.
     *
     * @var array<int, string>
     */
    private const ALLOWED_PROVIDERS = ['google'];

    /**
     * Redirect the user to the OAuth provider's consent screen.
     *
     * GET /auth/{provider}
     */
    public function redirect(string $provider = 'google'): RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->validateProvider($provider);

        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle the callback from the OAuth provider.
     *
     * GET /auth/{provider}/callback
     */
    public function callback(string $provider = 'google'): RedirectResponse
    {
        $this->validateProvider($provider);

        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error('Socialite callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return redirect('/login')
                ->with('error', 'Authentication with ' . ucfirst($provider) . ' failed. Please try again.');
        }

        // ── Find or Create User + Link Provider ──
        $user = $this->findOrCreateUser($socialiteUser, $provider);

        // ── Authenticate ──
        Auth::login($user, remember: true);

        Log::info('Socialite login success', [
            'user_id' => $user->id,
            'provider' => $provider,
        ]);

        return redirect()->intended('/dashboard');
    }

    /**
     * Find an existing user or create a new one, and link/update the OAuth provider.
     */
    private function findOrCreateUser(SocialiteUser $socialiteUser, string $provider): User
    {
        return DB::transaction(function () use ($socialiteUser, $provider): User {
            // ── Case 1: Provider link already exists → update tokens ──
            $existingLink = SocialiteProvider::query()
                ->where('provider', $provider)
                ->where('provider_id', (string) $socialiteUser->getId())
                ->first();

            if ($existingLink !== null) {
                $this->updateProviderTokens($existingLink, $socialiteUser);

                /** @var User $user */
                $user = $existingLink->user;

                // Update avatar if changed
                if ($socialiteUser->getAvatar() && $user->profile_photo_path !== $socialiteUser->getAvatar()) {
                    $user->update(['profile_photo_path' => $socialiteUser->getAvatar()]);
                }

                return $user;
            }

            // ── Case 2: User exists by email → link new provider ──
            $email = $socialiteUser->getEmail();
            $user = User::where('email', $email)->first();

            if ($user !== null) {
                $this->createProviderLink($user, $socialiteUser, $provider);
                return $user;
            }

            // ── Case 3: Brand new user → create User + Provider link ──
            $user = $this->createNewUser($socialiteUser);
            $this->createProviderLink($user, $socialiteUser, $provider);

            return $user;
        });
    }

    /**
     * Create a new User from OAuth data.
     */
    private function createNewUser(SocialiteUser $socialiteUser): User
    {
        $name = $socialiteUser->getName() ?? $socialiteUser->getNickname() ?? 'User';
        $nameParts = explode(' ', $name, 2);

        return User::create([
            'uuid' => (string) Str::uuid(),
            'name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? null,
            'email' => $socialiteUser->getEmail(),
            'email_verified_at' => now(), // OAuth emails are pre-verified
            'username' => $this->generateUniqueUsername($socialiteUser),
            'profile_photo_path' => $socialiteUser->getAvatar(),
            'password' => null, // Social-only user — no password
        ]);
    }

    /**
     * Create a new SocialiteProvider link for a user.
     */
    private function createProviderLink(User $user, SocialiteUser $socialiteUser, string $provider): SocialiteProvider
    {
        return SocialiteProvider::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => (string) $socialiteUser->getId(),
            'provider_email' => $socialiteUser->getEmail(),
            'nickname' => $socialiteUser->getNickname(),
            'avatar' => $socialiteUser->getAvatar(),
            'token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken,
            'token_expires_at' => $socialiteUser->expiresIn
                ? now()->addSeconds((int) $socialiteUser->expiresIn)
                : null,
        ]);
    }

    /**
     * Update token data on an existing provider link.
     */
    private function updateProviderTokens(SocialiteProvider $link, SocialiteUser $socialiteUser): void
    {
        $link->update([
            'token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken ?? $link->refresh_token,
            'avatar' => $socialiteUser->getAvatar() ?? $link->avatar,
            'nickname' => $socialiteUser->getNickname() ?? $link->nickname,
            'token_expires_at' => $socialiteUser->expiresIn
                ? now()->addSeconds((int) $socialiteUser->expiresIn)
                : $link->token_expires_at,
        ]);
    }

    /**
     * Generate a unique username from the OAuth user's name/email.
     */
    private function generateUniqueUsername(SocialiteUser $socialiteUser): string
    {
        $base = $socialiteUser->getNickname()
            ?? Str::before($socialiteUser->getEmail() ?? 'user', '@');

        $base = Str::slug($base, '_');
        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . '_' . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Validate the provider is in the allow-list.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function validateProvider(string $provider): void
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            abort(404, "OAuth provider [{$provider}] is not supported.");
        }
    }
}
