<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    /**
     * Menampilkan daftar toko yang berlangganan
     */
    public function index()
    {
        // Mengambil semua toko beserta jumlah user di dalamnya
        $tokos = Toko::withCount('users')->orderBy('created_at', 'desc')->get();
        
        return view('pages.cms.index', compact('tokos'));
    }

    /**
     * Memperbarui status dan tanggal kedaluwarsa langganan toko
     */
    public function updateSubscription(Request $request, $id)
    {
        $request->validate([
            'status_langganan' => 'required|string|max:50',
            'berakhir_pada'    => 'nullable|date',
        ]);

        $toko = Toko::findOrFail($id);
        
        $toko->update([
            'status_langganan' => $request->status_langganan,
            'berakhir_pada'    => $request->berakhir_pada,
        ]);

        return redirect()->back()->with('success', 'Status langganan untuk ' . $toko->nama_toko . ' berhasil diperbarui.');
    }
}
