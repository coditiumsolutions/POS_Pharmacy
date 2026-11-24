<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AspNetIdentityPasswordHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('pos.cash');
        }

        // Keep CSRF stable for this page load. Regenerating on every GET caused 419
        // when the browser reused a prior form (bfcache / second tab / cookie clash).
        if (!$request->session()->isStarted()) {
            $request->session()->start();
        }

        // Always serve the lightweight offline login page (no CDN fonts/CSS).
        // The marketing login page caused ERR_NETWORK_CHANGED on unstable networks.
        $response = response()
            ->view('auth.signin')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');

        return $this->forgetStaleLoginCookies($response);
    }

    /**
     * Drop leftover cookies from earlier deploys (domain=.coditium.com, old names).
     * Those survive a refresh in a normal browser but not in Incognito.
     */
    private function forgetStaleLoginCookies($response)
    {
        $names = [
            'pos_pharmacy_session',
            'XSRF-TOKEN',
            'laravel_session',
            'pharmacy_session',
        ];
        $domains = [null, 'pharmacy.coditium.com', '.coditium.com', '.pharmacy.coditium.com'];

        $expired = time() - 3600;
        foreach ($names as $name) {
            foreach ($domains as $domain) {
                $response->headers->setCookie(new Cookie(
                    $name,
                    '',
                    $expired,
                    '/',
                    $domain,
                    true,
                    true,
                    false,
                    'lax'
                ));
            }
        }

        return $response;
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string',
        ]);

        $userName = trim((string) $request->input('user_name'));
        // Prefer login_pass so browser-saved "password" autofill cannot override what the user typed.
        $password = trim((string) $request->input('login_pass', ''));
        if ($password === '') {
            $password = trim((string) $request->input('password', ''));
        }

        if ($password === '') {
            return back()->withErrors([
                'user_name' => 'Password is required.',
            ])->withInput(['user_name' => $userName]);
        }

        $user = User::query()
            ->where(function ($q) use ($userName) {
                $q->whereRaw('LOWER(TRIM(user_name)) = ?', [mb_strtolower($userName)])
                    ->orWhereRaw('LOWER(TRIM(email)) = ?', [mb_strtolower($userName)]);
            })
            ->where('is_active', 1)
            ->first();

        if ($user && $this->passwordMatches($password, $user->password_hash)) {
            // Upgrade ASP.NET / legacy SHA-256 hashes to bcrypt after a successful login.
            if (AspNetIdentityPasswordHasher::isAspNetHash($user->password_hash)
                || AspNetIdentityPasswordHasher::isLegacySha256Hash($user->password_hash)) {
                $user->password_hash = Hash::make($password);
                $user->save();
            } elseif (!str_starts_with((string) $user->password_hash, '$2y$')
                && !str_starts_with((string) $user->password_hash, '$2a$')
                && !str_starts_with((string) $user->password_hash, '$2b$')) {
                // Normalize unknown-but-matching hashes to bcrypt.
                $user->password_hash = Hash::make($password);
                $user->save();
            }

            Auth::login($user, false);
            $request->session()->regenerate();

            $user->last_login_date = now();
            $user->save();

            return redirect()->intended(route('pos.cash'));
        }

        return back()->withErrors([
            'user_name' => 'Invalid username or password.',
        ])->withInput(['user_name' => $userName]);
    }

    private function passwordMatches(string $plain, ?string $hashed): bool
    {
        if ($hashed === null || $hashed === '') {
            return false;
        }

        if (AspNetIdentityPasswordHasher::isAspNetHash($hashed)) {
            return AspNetIdentityPasswordHasher::verify($plain, $hashed);
        }

        // bcrypt / argon (Laravel Hash::make) — also try native password_verify
        if (str_starts_with($hashed, '$2y$')
            || str_starts_with($hashed, '$2a$')
            || str_starts_with($hashed, '$2b$')
            || str_starts_with($hashed, '$argon')) {
            return Hash::check($plain, $hashed) || password_verify($plain, $hashed);
        }

        // Older POS_MT passwords: uppercase SHA-256 hex
        if (AspNetIdentityPasswordHasher::isLegacySha256Hash($hashed)) {
            return AspNetIdentityPasswordHasher::verifyLegacySha256($plain, $hashed);
        }

        return false;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
