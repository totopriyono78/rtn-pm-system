<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Setara EnsureUserIsActive tapi untuk guard 'client' (Client Portal, SRS
 * 4.3) -- menolak akses jika akun client sudah dinonaktifkan oleh
 * Project Controller/Administrator lewat halaman Kelola Akun Client.
 */
class EnsureClientIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = Auth::guard('client')->user();

        if ($client && ! $client->is_active) {
            Auth::guard('client')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('client.login')->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi PT RTN.',
            ]);
        }

        return $next($request);
    }
}
