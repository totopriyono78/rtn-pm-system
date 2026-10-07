<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Login/logout untuk Client Portal (SRS 4.3), guard terpisah 'client'.
 * Sengaja dibuat terpisah dari AuthenticatedSessionController (guard 'web')
 * supaya tidak ada risiko tercampurnya sesi karyawan internal dan customer.
 */
class ClientAuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('client.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('client')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $client = Auth::guard('client')->user();

        if (! $client->is_active) {
            Auth::guard('client')->logout();
            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi PT RTN.',
            ]);
        }

        $client->forceFill(['last_login_at' => now()])->save();

        $request->session()->regenerate();

        // SENGAJA TIDAK pakai redirect()->intended(): session key 'url.intended'
        // dipakai BERSAMA oleh semua guard (web & client sekaligus) -- kalau
        // sebelumnya ada upaya akses halaman staf yang gagal (guard 'web'),
        // key itu bisa berisi URL staf, dan dipakai di sini akan melempar
        // customer ke halaman staf (yang langsung menolaknya balik ke /login).
        // Client Portal tidak butuh deep-link setelah login, jadi langsung saja.
        return redirect()->route('client.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('client')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }
}
