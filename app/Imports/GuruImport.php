<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Lewati baris jika data email atau nama kosong
        if (!isset($row['email']) || !isset($row['nama_guru'])) {
            return null;
        }

        // Cek apakah email sudah ada agar tidak error duplikat
        $user = User::firstOrCreate(
            ['email' => $row['email']],
            [
                'name' => $row['nama_guru'],
                'password' => Hash::make('guru123')
            ]
        );

        // Jika user ini baru dibuat dan belum punya role, beri role guru
        if (!$user->hasRole('guru')) {
            $user->assignRole('guru');
        }

        // Simpan atau update profil guru
        Guru::updateOrCreate(
            [
                'users_id' => $user->id,
                'nama_guru' => $row['nama_guru'],
                'jenis_kelamin' => $row['jenis_kelamin'] ?? 'L',
                'status' => $row['status'] ?? 'Tetap',
            ]
        );

        return null;
    }
    public function rules(): array
    {
        return [
            'nama_guru'     => 'required|min:3|string',
            'email'         => 'required|email|unique:users,email',
            'jenis_kelamin' => 'required|in:L,P',
            'status'        => 'required|in:Tetap,Honorer',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_guru.required' => 'Nama Guru tidak boleh kosong.',
            'nama_guru.min' => 'Nama Guru minimal 3 karakter.',
            'email.required' => 'Email tidak boleh kosong.',
            'email.unique' => 'Email sudah terdaftar.',
            'jenis_kelamin.required' => 'Jenis Kelamin tidak boleh kosong.',
            'status.required' => 'Status Guru tidak boleh kosong.',
        ];
    }
}
