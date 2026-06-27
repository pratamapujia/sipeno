<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password harus diisi',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            // dd($user->getRoleNames());

            // Pengalihan halaman utama berdasarkan role
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard')->with('success', 'Selamat datang kembali, ' . $user->name . 'di Dashboard Admin');
            }
            if ($user->hasRole('guru')) {
                return redirect()->route('guru.dashboard')->with('success', 'Selamat datang kembali, ' . $user->name . 'di Dashboard Guru');
            }
            if ($user->hasRole('kepsek')) {
                return redirect()->route('kepsek.dashboard')->with('success', 'Selamat datang kembali, ' . $user->name . 'di Dashboard Kepsek');
            }
            Auth::logout();
            return back()->withErrors([
                'error' => 'Akun Anda berhasil diautentikasi, namun belum memiliki hak akses (role) yang valid di sistem.',
            ]);
        }

        return back()->withErrors([
            'error' => 'Email atau password yang anda masukkan salah.',
        ])->onlyInput('email');
    }

    // Fungsi untuk logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout');
    }
}
