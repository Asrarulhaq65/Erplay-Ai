<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman pendaftaran toko
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Proses pendaftaran toko baru dan akun owner
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama_toko'    => ['required', 'string', 'max:255'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'username'     => ['required', 'string', 'max:50', 'unique:users,username'],
            'password'     => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'username.unique'    => 'Username ini sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.'
        ]);

        // 1. Buat Toko Baru
        $toko = Toko::create([
            'nama_toko' => $validated['nama_toko'],
            'alamat'    => 'Alamat belum diisi', // Default
            'no_telepon'=> '-', // Default
            'catalog_hero_description' => 'Lihat produk, cek harga, dan tanyakan ketersediaan langsung ke asisten toko kami.',
        ]);
        $toko->update(['catalog_slug' => Str::slug($validated['nama_toko']) . '-' . $toko->id]);

        // 2. Cari Role Owner
        $roleOwner = Role::where('nama_role', 'Owner')->first();

        // 3. Buat User (Owner) dan tautkan ke Toko baru
        $user = User::create([
            'toko_id'      => $toko->id,
            'role_id'      => $roleOwner ? $roleOwner->id : null,
            'username'     => $validated['username'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'password'     => Hash::make($validated['password']),
            'is_active'    => true,
        ]);

        // 4. Otomatis Login
        Auth::login($user);

        return redirect()->intended('/dashboard')->with('success', 'Toko berhasil didaftarkan! Selamat datang di dashboard Anda.');
    }

    /**
     * Tampilkan halaman login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses percobaan login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Jika user is_active = false, batalkan login
            if (!auth()->user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'username' => 'Akun Anda telah dinonaktifkan.',
                ])->onlyInput('username');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    /**
     * Proses logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil logout.');
    }
}
