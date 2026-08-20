<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCatalogSettingController extends Controller
{
    public function edit(): View
    {
        return view('pages.pengaturan.katalog.edit', ['toko' => auth()->user()->toko]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'catalog_slug' => ['required', 'string', 'alpha_dash', 'max:100', 'unique:toko,catalog_slug,' . auth()->user()->toko->id],
            'catalog_hero_badge' => ['required', 'string', 'max:100'],
            'catalog_hero_title' => ['required', 'string', 'max:180'],
            'catalog_hero_description' => ['required', 'string', 'max:500'],
            'catalog_theme' => ['required', 'in:default'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'catalog_enabled' => ['nullable', 'boolean'],
            'whatsapp_enabled' => ['nullable', 'boolean'],
        ]);
        $validated['catalog_enabled'] = $request->boolean('catalog_enabled');
        $validated['whatsapp_enabled'] = $request->boolean('whatsapp_enabled');
        auth()->user()->toko->update($validated);

        return redirect()->route('pengaturan.katalog.edit')->with('success', 'Pengaturan katalog publik berhasil diperbarui.');
    }
}
