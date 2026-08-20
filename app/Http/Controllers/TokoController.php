<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TokoController extends Controller
{
    /**
     * Show the form for editing the current Toko.
     */
    public function edit()
    {
        $toko = auth()->user()->toko;
        
        if (!$toko) {
            return redirect('/')->with('error', 'Toko tidak ditemukan.');
        }

        return view('pages.pengaturan.toko.edit', compact('toko'));
    }

    /**
     * Update the current Toko.
     */
    public function update(Request $request)
    {
        $toko = auth()->user()->toko;

        if (!$toko) {
            return redirect('/')->with('error', 'Toko tidak ditemukan.');
        }

        $validated = $request->validate([
            'nama_toko'    => 'required|string|max:100',
            'alamat'       => 'required|string',
            'no_telepon'   => 'required|string|max:20',
            'slogan_struk' => 'nullable|string|max:150',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($toko->logo && Storage::disk('public')->exists($toko->logo)) {
                Storage::disk('public')->delete($toko->logo);
            }

            // Store new logo
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $path;
        }

        $toko->update($validated);

        return redirect()->route('pengaturan.toko.edit')->with('success', 'Profil toko berhasil diperbarui.');
    }
}
