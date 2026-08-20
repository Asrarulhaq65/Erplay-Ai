<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Super Admin bebas dari pengecekan langganan
        if ($user && !$user->isSuperAdmin()) {
            $toko = $user->toko;

            if ($toko) {
                $isExpired = $toko->berakhir_pada && $toko->berakhir_pada->isPast();
                $isStatusBlocked = in_array($toko->status_langganan, ['Kedaluwarsa', 'Nonaktif']);

                if ($isExpired || $isStatusBlocked) {
                    // Update status to Kedaluwarsa automatically if date is past but status is still Aktif
                    if ($isExpired && $toko->status_langganan !== 'Kedaluwarsa' && $toko->status_langganan !== 'Nonaktif') {
                        $toko->update(['status_langganan' => 'Kedaluwarsa']);
                    }

                    return redirect()->route('langganan.index')->with('error', 'Masa berlangganan toko Anda telah habis atau dinonaktifkan.');
                }
            }
        }

        return $next($request);
    }
}
