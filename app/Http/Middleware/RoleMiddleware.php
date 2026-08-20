<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware
 *
 * Melindungi route berdasarkan nama role.
 * Penggunaan di route:
 *   ->middleware('role:Super Admin')
 *   ->middleware('role:Super Admin,Owner')   ← beberapa role dipisah koma
 *
 * Jika user tidak memiliki role yang diperlukan, akan di-redirect ke '/'
 * dengan flash message 'error'.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $userRole = $user->role?->nama_role;

        if (!in_array($userRole, $roles)) {
            return redirect('/')->with('error',
                "Akses ditolak. Halaman ini memerlukan role: " . implode(' / ', $roles) . "."
            );
        }

        return $next($request);
    }
}
