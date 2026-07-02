<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Menampilkan halaman edit profil
    public function edit()
    {
        $user = Auth::user();
        $guru = $user->guru;
        return view('guru.profile', compact('guru', 'user'));
    }

    // Memproses perubahan password
    public function update(Request $request)
    {
        // Validasi input
        $request->validate([
            'current_password' => ['required', 'current_password'], // Mengecek password lama di database
            'password' => ['required', 'min:8', 'confirmed'],       // Minimal 8 karakter & cocok dengan konfirmasi
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini yang Anda masukkan salah.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Proses update password
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password Anda berhasil diperbarui! Silakan gunakan password baru pada login berikutnya.');
    }
}
